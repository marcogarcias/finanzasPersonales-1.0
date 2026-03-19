<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\BovedaController;
use App\Http\Controllers\ProveedorController;

// Rutas de Autenticación
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rutas protegidas por login y por licencia
Route::middleware(['auth', 'license'])->group(function () {
    
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/descargas', [DownloadController::class, 'index'])->name('download.index');
    Route::post('/download', [DownloadController::class, 'dispatchDownload'])->name('download.start');
    Route::get('/download/status/{jobId}', [DownloadController::class, 'checkStatus'])->name('download.status');
    Route::post('/captcha/submit', [DownloadController::class, 'submitCaptcha'])->name('captcha.submit');
    Route::get('/select-folder', [DownloadController::class, 'selectFolder'])->name('folder.select');
    
    Route::get('/boveda', [BovedaController::class, 'index'])->name('boveda.index');
    Route::get('/api/boveda/scan', [BovedaController::class, 'scan'])->name('api.boveda.scan');
    Route::get('/api/boveda/files', [BovedaController::class, 'getFiles'])->name('api.boveda.files');
    Route::get('/api/boveda/export', [BovedaController::class, 'export'])->name('api.boveda.export');
    Route::delete('/api/boveda', [BovedaController::class, 'bulkDestroy'])->name('api.boveda.bulkDestroy');
    Route::post('/api/boveda/check-status', [BovedaController::class, 'bulkCheckStatus'])->name('api.boveda.bulkCheckStatus');
    Route::delete('/api/boveda/{id}', [BovedaController::class, 'destroy'])->name('api.boveda.destroy');
    
    // Rutas de Proveedores
    Route::get('/proveedores', [ProveedorController::class, 'index'])->name('proveedores.index');
    Route::get('/api/proveedores', [ProveedorController::class, 'getProveedores'])->name('api.proveedores.index');
    Route::get('/api/proveedores/conceptos', [ProveedorController::class, 'getConceptos'])->name('api.proveedores.conceptos');
    Route::put('/api/proveedores/{id}', [ProveedorController::class, 'update'])->name('api.proveedores.update');
    Route::delete('/api/proveedores/{id}', [ProveedorController::class, 'destroy'])->name('api.proveedores.destroy');

});

