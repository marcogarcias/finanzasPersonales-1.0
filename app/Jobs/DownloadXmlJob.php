<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpCfdi\CfdiSatScraper\Filters\DownloadType;
use PhpCfdi\CfdiSatScraper\Filters\Options\DownloadTypesOption;
use PhpCfdi\CfdiSatScraper\MetadataList;
use PhpCfdi\CfdiSatScraper\QueryByFilters;
use PhpCfdi\CfdiSatScraper\SatScraper;
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionData; // Importar esto
use PhpCfdi\CfdiSatScraper\Sessions\Fiel\FielSessionManager;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionData;
use PhpCfdi\CfdiSatScraper\Sessions\Ciec\CiecSessionManager;
use PhpCfdi\Credentials\Credential;
use DateTimeImmutable;
use Throwable;
use App\Models\Comprobante;
use App\Services\CfdiParser;
use Illuminate\Support\Facades\File;

class DownloadXmlJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Aumentar tiempo límite a 1 hora (3600s) para descargas masivas
    public $timeout = 3600;
    
    // Evitar reintentos infinitos si falla (opcional, dejamos default o ponemos 1)
    public $tries = 1;

    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Forzar tiempo de ejecución ilimitado para este proceso
        set_time_limit(0);
        
        $jobId = $this->payload['job_id'];
        
        try {
            $authMode = $this->payload['auth_mode'] ?? 'fiel';
            $modeLabel = ($authMode === 'ciec') ? 'Contraseña (CIEC)' : 'e.firma (FIEL)';
            $this->updateStatus($jobId, 'started', 5, "Autenticando con {$modeLabel}...");

            $scraper = null;
            $list = null;
            $maxCaptchaRetries = 10;
            $captchaRetryCount = 0;

            while ($captchaRetryCount < $maxCaptchaRetries) {
                try {
                    if ($authMode === 'ciec') {
                        $rfc = $this->payload['rfc'];
                        $password = $this->payload['password'];
                        $captchaResolver = new \App\Services\InteractiveCaptchaResolver($jobId);
                        $sessionData = new CiecSessionData($rfc, $password, $captchaResolver);
                        $sessionManager = new CiecSessionManager($sessionData);
                    } else {
                        $credential = Credential::openFiles(
                            $this->payload['cert_path'],
                            $this->payload['key_path'],
                            $this->payload['password']
                        );
                        $rfc = $credential->rfc();
                        $sessionData = new FielSessionData($credential);
                        $sessionManager = new FielSessionManager($sessionData);
                    }
                    
                    $scraper = new SatScraper($sessionManager);
                    $this->updateStatus($jobId, 'searching', 15, "Autenticado como {$rfc}. Buscando CFDI...");

                    // 3. Configurar Query
                    $start = new DateTimeImmutable($this->payload['start_date']);
                    $end = new DateTimeImmutable($this->payload['end_date']);
                    
                    $downloadType = $this->payload['download_type'] === 'emitidos' 
                        ? DownloadType::emitidos() 
                        : DownloadType::recibidos();

                    $query = new QueryByFilters($start, $end);
                    $query->setDownloadType($downloadType);

                    // 4. Obtener Lista de CFDI (Metadata) - Esto dispara el LOGIN y el CAPTCHA
                    $list = $scraper->listByPeriod($query);
                    
                    // Si llegamos aquí, login exitoso
                    break;

                } catch (\Throwable $e) {
                    // Si nosotros pedimos refrescar (o la librería envolvió nuestra petición)
                    $msg = $e->getMessage();
                    $prev = $e->getPrevious()?->getMessage();
                    
                    if ($msg === 'CAPTCHA_REFRESH_REQUESTED' || $prev === 'CAPTCHA_REFRESH_REQUESTED') {
                        $captchaRetryCount++;
                        $this->updateStatus($jobId, 'awaiting_captcha', 15, "Solicitando nueva imagen al SAT (Intento {$captchaRetryCount})...");
                        continue;
                    }

                    if ($msg === 'CAPTCHA_CANCELLED' || $prev === 'CAPTCHA_CANCELLED') {
                        $this->updateStatus($jobId, 'failed', 0, "Descarga cancelada por el usuario.");
                        return; // Termina el Job completamente
                    }

                    // Si es otro error (como error de login real del SAT), lanzamos la excepción
                    throw $e;
                }
            }
            $total = count($list);

            if ($total === 0) {
                $this->updateStatus($jobId, 'completed', 100, 'No se encontraron facturas en este periodo.');
                return;
            }

            // 5. Descargar XMLs
            // Lógica de ruta: Custom Path usuario > Documentos Defecto > Storage App
            $userProfile = getenv('USERPROFILE');
            
            // Determinar nombre de carpeta por tipo
            $tipoCarpeta = ($this->payload['download_type'] === 'emitidos') ? 'Ingresos' : 'Egresos';
            
            if (!empty($this->payload['custom_path'])) {
                // User Path: Custom/bovedaSAT/RFC/Tipo
                // CORRECTO: Quitamos cualquier intermedio extra
                $basePath = $this->payload['custom_path'] . '/bovedaSAT/' . $rfc . '/' . $tipoCarpeta;
            } else {
                // Default: Documentos/bovedaSAT/RFC/Tipo
                $basePath = $userProfile 
                    ? $userProfile . '/Documents/bovedaSAT/' . $rfc . '/' . $tipoCarpeta
                    : storage_path('app/bovedaSAT/' . $rfc . '/' . $tipoCarpeta);
            }

            // Agrupar por Año/Mes (YYYY/MM)
            // Fecha de Emisión es clave para ordenar
            $collection = collect($list);
            $total = $collection->count();
            $downloadedCount = 0;

            // Agrupamos la colección por fecha "YYYY/MM"
            $gruposPorFecha = $collection->groupBy(function ($metadata) {
                // Metadata es un objeto, usamos get() para obtener valores
                $fecha = $metadata->get('fechaEmision'); 
                if (!$fecha) return 'SinFecha';
                
                try {
                    return (new DateTimeImmutable($fecha))->format('Y/m'); // Ejemplo: 2024/01
                } catch (\Exception $e) {
                    return 'SinFecha';
                }
            });

            // Iteramos por grupo (Carpeta de Mes)
            foreach ($gruposPorFecha as $folderDate => $itemsInGroup) {
                
                // Definir carpeta destino final: Base/2024/01
                $targetPath = "{$basePath}/{$folderDate}";
                
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0777, true);
                }

                // Procesar el grupo en chunks pequeños para mantener el feedback de progreso en UI
                $chunks = $itemsInGroup->chunk(10);

                foreach ($chunks as $chunkList) {
                    $smallMetadataList = new MetadataList($chunkList->all());

                    $scraper->resourceDownloader(\PhpCfdi\CfdiSatScraper\ResourceType::xml(), $smallMetadataList)
                        ->setConcurrency(5)
                        ->saveTo($targetPath);
                    
                    // --- NUEVA LÓGICA: Sincronizar con Base de Datos ---
                    $clase = ($this->payload['download_type'] === 'emitidos') ? 'emitido' : 'recibido';
                    foreach ($chunkList as $metadata) {
                        $uuid = $metadata->get('uuid');
                        $filePath = "{$targetPath}/{$uuid}.xml";
                        
                        // Si el archivo se guardó correctamente, lo procesamos
                        if (File::exists($filePath)) {
                            try {
                                $xmlContent = File::get($filePath);
                                $satStatus = $metadata->get('estatus'); // Obtener estatus real desde metadata del SAT
                                
                                $parsedData = CfdiParser::parse(
                                    $xmlContent, 
                                    $this->payload['user_id'], 
                                    $clase, 
                                    $filePath,
                                    $satStatus
                                );

                                // Guardar o actualizar en BD (Soportando registros previamente eliminados suavemente)
                                // Usamos el UUID extraído del XML para la búsqueda por mayor precisión
                                $uuidFromXml = $parsedData['uuid'];
                                $comprobante = Comprobante::withTrashed()->where('uuid', 'LIKE', $uuidFromXml)->first();
                                
                                if ($comprobante) {
                                    if ($comprobante->trashed()) {
                                        $comprobante->restore();
                                    }
                                    $comprobante->update($parsedData);
                                } else {
                                    Comprobante::create($parsedData);
                                }
                            } catch (\Exception $e) {
                                \Illuminate\Support\Facades\Log::error("Error procesando XML {$uuid}: " . $e->getMessage());
                            }
                        }
                    }
                    // --------------------------------------------------

                    // Actualizar progreso total
                    $downloadedCount += count($chunkList);
                    $progress = 20 + (($downloadedCount / $total) * 80); 
                    
                    $this->updateStatus($jobId, 'downloading', (int)$progress, "Procesando {$folderDate} ({$downloadedCount}/{$total})...");
                }
            }
            
            $this->updateStatus($jobId, 'completed', 100, "Descarga completada. {$total} archivos en Bóveda SAT.");


        } catch (Throwable $e) {
            $this->updateStatus($jobId, 'failed', 0, 'Error crítico: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e);
        } finally {
            // Asegurarnos de cerrar cualquier recurso si fuera necesario
        }
    }

    protected function updateStatus($jobId, $status, $progress, $message)
    {
        Cache::put("download_status_{$jobId}", [
            'status' => $status,
            'progress' => $progress,
            'message' => $message,
            'downloaded_count' => 0 // Podríamos completarlo
        ], 3600);
    }
}
