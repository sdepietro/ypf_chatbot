<?php

use App\Http\Controllers\Web\AgentPageController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ChatPageController;
use App\Http\Controllers\Web\ConfigPageController;
use App\Http\Controllers\Web\DashboardPageController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

// Auth routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected routes
Route::middleware('master.auth')->group(function () {
    Route::get('/', [ChatPageController::class, 'index'])->name('chat.index');
    Route::get('/dashboard', [DashboardPageController::class, 'index'])->name('dashboard.index');
    Route::get('/agents', [AgentPageController::class, 'index'])->name('agents.index');
    Route::get('/configs', [ConfigPageController::class, 'index'])->name('configs.index');
});



Route::get('/forceinstall', function () {

    echo 'Forcing installation...<br>';

    // Ejecutar el comando para vaciar la base de datos y migrar desde cero
    Artisan::call('migrate:fresh', ['--force' => true]);

    echo "Database reset and installation success!";
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    //$exitCode = Artisan::call('route:cache');
    Artisan::call('route:clear');
    //dd("--");
    Artisan::call('view:clear');
    Artisan::call('config:cache');
    Artisan::call('vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"');
    dd("Cache borrada correctamente!!!");
    //$routeCollection = Route::getRoutes();
    //dd($routeCollection);
    // return what you want
});

