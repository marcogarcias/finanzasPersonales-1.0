<?php

namespace App\Providers;

use Native\Laravel\Facades\Window;
use Native\Laravel\Facades\Screen;
use Native\Laravel\Contracts\ProvidesPhpIni;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        // Valores por defecto en caso de que falle la detección de pantalla
        $width = 1080;
        $height = 900;

        try {
            $screen = Screen::primary();
            if ($screen) {
                $width = (int) ($screen->width * 0.8);
                $height = (int) ($screen->height * 0.8);
            }
        } catch (\Exception $e) {
            // Si falla la detección, se mantienen los valores por defecto
        }

        Window::open()
            ->width($width)
            ->height($height)
            ->title('ASES - Finanzas Personales')
            ->resizable();
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }
}
