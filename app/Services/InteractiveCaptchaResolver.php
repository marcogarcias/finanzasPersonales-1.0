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
        $imageContent = $image->asBinary();
        $base64Image = 'data:image/png;base64,' . base64_encode($imageContent);

        $statusKey = "download_status_{$this->jobId}";
        $status = Cache::get($statusKey);
        $status['status'] = 'awaiting_captcha';
        $status['captcha_url'] = $base64Image;
        $status['message'] = 'Por favor resuelve el captcha para continuar';
        Cache::put($statusKey, $status, 300);

        $answerKey = "captcha_answer_{$this->jobId}";
        // Eliminamos el forget de aquí para evitar borrar señales de cancelación enviadas rápido 
        // antes de entrar al loop.

        for ($i = 0; $i < 120; $i++) {
            // 1. Revisar si el estatus general cambió a fallido/cancelado (doble seguridad)
            $status = Cache::get($statusKey);
            if ($status && $status['status'] === 'failed') {
                 throw new \RuntimeException('CAPTCHA_CANCELLED');
            }

            // 2. Revisar si hay una respuesta (incluyendo CANCEL o REFRESH)
            $answer = Cache::get($answerKey);
            
            if ($answer) {
                Cache::forget($answerKey);
                
                if ($answer === 'REFRESH') {
                    throw new \RuntimeException('CAPTCHA_REFRESH_REQUESTED');
                }
                
                if ($answer === 'CANCEL') {
                    throw new \RuntimeException('CAPTCHA_CANCELLED');
                }
                
                return new CaptchaAnswer($answer);
            }
            sleep(1);
        }

        throw new \Exception("Tiempo de espera para el captcha agotado.");
    }
}
