<?php

namespace App\Http\Controllers;

use App\Jobs\DownloadXmlJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Native\Laravel\Dialog; // Clase directa

class DownloadController extends Controller
{
    /**
     * Muestra la vista principal de descargas.
     */
    public function index()
    {
        $userId = auth()->id();
        $query = \App\Models\Comprobante::where('user_id', $userId);
        
        $emitidos = (clone $query)->where('clase_comprobante', 'emitido')->distinct()->pluck('rfc_emisor');
        $recibidos = (clone $query)->where('clase_comprobante', 'recibido')->distinct()->pluck('rfc_receptor');
        
        $rfcs = $emitidos->merge($recibidos)->unique()->sort()->values();

        return view('downloads', compact('rfcs'));
    }

    /**
     * Recibe la petición del formulario, valida y despacha el Job a la cola.
     */
    public function dispatchDownload(Request $request)
    {
        $authMode = $request->input('auth_mode', 'ciec');

        // Validación dinámica según modo
        $rules = [
            'auth_mode' => 'required|in:ciec,fiel',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'download_type' => 'required|in:emitidos,recibidos',
        ];

        if ($authMode === 'ciec') {
            $rules['rfc'] = 'required|string|size:13';
            $rules['password_ciec'] = 'required|string';
        } else {
            $rules['certificate'] = 'required|file';
            $rules['private_key'] = 'required|file';
            $rules['password_fiel'] = 'required|string';
        }

        $request->validate($rules);

        // Generar un ID único para este trabajo de descarga
        $jobId = (string) Str::uuid();

        $payload = [
            'job_id' => $jobId,
            'auth_mode' => $authMode,
            'start_date' => $request->input('start_date'),
            'end_date' => $request->input('end_date'),
            'download_type' => $request->input('download_type'),
            'custom_path' => $request->input('custom_path'),
            'user_id' => auth()->id(),
        ];

        if ($authMode === 'ciec') {
            $payload['rfc'] = $request->input('rfc');
            $payload['password'] = $request->input('password_ciec');
        } else {
            // Guardar archivos temporales (FIEL)
            $certPath = $request->file('certificate')->storeAs('temp_fiel', $jobId . '.cer');
            $keyPath = $request->file('private_key')->storeAs('temp_fiel', $jobId . '.key');
            
            $payload['cert_path'] = storage_path('app/' . $certPath);
            $payload['key_path'] = storage_path('app/' . $keyPath);
            $payload['password'] = $request->input('password_fiel');
        }

        // Inicializar estatus en Caché
        Cache::put("download_status_{$jobId}", [
            'status' => 'queued',
            'progress' => 0,
            'message' => 'En cola...',
            'downloaded_count' => 0,
        ], 3600); // 1 hora de vida

        // Despachar el Job
        DownloadXmlJob::dispatch($payload);

        return response()->json([
            'success' => true,
            'job_id' => $jobId,
            'message' => 'La descarga se ha iniciado en segundo plano.'
        ]);
    }

    /**
     * Endpoint para que el frontend consulte el progreso (Polling).
     */
    public function checkStatus($jobId)
    {
        $status = Cache::get("download_status_{$jobId}");

        if (!$status) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json($status);
    }

    /**
     * Abre un diálogo nativo para seleccionar carpeta.
     */
    public function selectFolder()
    {
        try {
            // Instanciamos vía contenedor para resolver dependencias
            $path = app(Dialog::class)
                ->title('Seleccionar carpeta de destino')
                ->button('Seleccionar Carpeta')
                ->folders() 
                ->open();

            return response()->json(['path' => $path]);
        } catch (\Exception $e) {
            // Fallback si no hay NativePHP activo (ej. navegador web normal)
            return response()->json(['path' => null, 'error' => 'No disponible en web']);
        }
    }
    /**
     * Recibe la respuesta del captcha desde el frontend.
     */
    public function submitCaptcha(Request $request)
    {
        $request->validate([
            'job_id' => 'required|string',
            'captcha_answer' => 'required|string',
        ]);

        $jobId = $request->input('job_id');
        $answer = $request->input('captcha_answer');

        // Guardar la respuesta en caché para que el Job la tome
        \Illuminate\Support\Facades\Cache::put("captcha_answer_{$jobId}", $answer, 300);

        // Actualizar el estatus para informar al polling
        $statusKey = "download_status_{$jobId}";
        $status = \Illuminate\Support\Facades\Cache::get($statusKey);
        if ($status) {
            if ($answer === 'REFRESH') {
                $status['message'] = 'Solicitando nuevo captcha...';
            } elseif ($answer === 'CANCEL') {
                $status['status'] = 'failed';
                $status['message'] = 'Cancelando descarga...';
            } else {
                $status['status'] = 'processing';
                $status['message'] = 'Captcha recibido, continuando...';
            }
            \Illuminate\Support\Facades\Cache::put($statusKey, $status, 300);
        }

        return response()->json(['success' => true]);
    }
}
