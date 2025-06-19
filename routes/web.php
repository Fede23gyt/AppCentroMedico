<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\PrestacionController;
use App\Http\Controllers\PrestacionPlanController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login')->middleware('guest');

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('guest');

Route::post('/logout', [AuthController::class, 'logout'])
    ->name('logout')->middleware('auth');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // Rutas de usuarios
    Route::middleware(['permission:users.view'])->group(function () {
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])
            ->name('users.toggle-status');
    });

    // Rutas de sucursales
    Route::resource('sucursales', SucursalController::class)
        ->parameters(['sucursales' => 'sucursal']);

    Route::patch('sucursales/{sucursal}/toggle-status', [SucursalController::class, 'toggleStatus'])
        ->name('sucursales.toggle-status');
    Route::get('sucursales/{sucursal}/users', [SucursalController::class, 'getUsers'])
        ->name('sucursales.users');

    // Rubros
    Route::resource('rubros', RubroController::class);
    Route::get('api/rubros', [RubroController::class, 'apiList'])->name('api.rubros');

    // Planes
    Route::resource('planes', PlanController::class);
    Route::get('api/planes', [PlanController::class, 'apiList'])->name('api.planes');

    // Prestaciones

    // Ruta para verificar código de prestación
    Route::get('prestaciones/check-codigo', [PrestacionController::class, 'checkCodigo'])
        ->name('prestaciones.check-codigo');

    Route::resource('prestaciones', PrestacionController::class)
        ->parameters(['prestaciones' => 'prestacion']);
    Route::get('prestaciones/{prestacion}/planes', [PrestacionController::class, 'planes'])->name('prestaciones.planes');
    Route::post('prestaciones/{prestacion}/planes', [PrestacionController::class, 'asociarPlan'])->name('prestaciones.asociar-plan');
    Route::delete('prestaciones/{prestacion}/planes/{plan}', [PrestacionController::class, 'desasociarPlan'])->name('prestaciones.desasociar-plan');
    Route::put('prestaciones/{prestacion}/planes/{plan}', [PrestacionController::class, 'actualizarPlan'])->name('prestaciones.actualizar-plan');
    Route::get('api/prestaciones', [PrestacionController::class, 'apiList'])->name('api.prestaciones');

});
