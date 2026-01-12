<?php

namespace App\Services;

use PhpCfdi\ImageCaptchaResolver\CaptchaAnswer;
use PhpCfdi\ImageCaptchaResolver\CaptchaAnswerInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaImageInterface;
use PhpCfdi\ImageCaptchaResolver\CaptchaResolverInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class InteractiveCaptchaResolver implements CaptchaResolverInterface
{
    protected string $jobId;

    public function __construct(string $jobId)
    {
        $this->jobId = $jobId;
    }

    public function resolve(CaptchaImageInterface $image): CaptchaAnswerInterface
    {
        // 1. Convertir la imagen a Base64
        $imageContent = $image->asBinary();
        $base64Image = 'data:image/png;base64,' . base64_encode($imageContent);

        // 2. Notificar a la UI que necesitamos el captcha
        $statusKey = "download_status_{$this->jobId}";
        $status = Cache::get($statusKey);
        $status['status'] = 'awaiting_captcha';
        $status['captcha_url'] = $base64Image; // Enviamos el base64 directo
        $status['message'] = 'Por favor resuelve el captcha para continuar';
        Cache::put($statusKey, $status, 300);

        // 3. Esperar a que el usuario responda (máximo 60 segundos)
        $answerKey = "captcha_answer_{$this->jobId}";
        Cache::forget($answerKey); // Limpiar por si acaso

        for ($i = 0; $i < 60; $i++) {
            $answer = Cache::get($answerKey);
            if ($answer) {
                Cache::forget($answerKey);
                return new CaptchaAnswer($answer);
            }
            sleep(1);
        }

        throw new \Exception("Tiempo de espera para el captcha agotado.");
    }
}
