<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\TituloController;

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

// Página inicial redirecionará para login ou dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
})->name('home');

// Rotas de autenticação
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Rotas protegidas (requerem autenticação)
Route::middleware(['auth', 'verify.api.key'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Perfil do cliente
    Route::get('/meus-dados', [ClienteController::class, 'show'])->name('cliente.dados');
    
    // Clientes (para admin e vendedores)
    Route::get('/clientes/{cliente_id}', [ClienteController::class, 'showById'])
        ->name('cliente.show')
        ->middleware('check.client.access');
    
    // Títulos em aberto
    Route::get('/titulos/em-aberto', [TituloController::class, 'emAberto'])->name('titulos.em-aberto');
    
    // Boletos pagos
    Route::get('/titulos/pagos', [TituloController::class, 'pagos'])->name('titulos.pagos');
    
    // Gerar boleto
    Route::get('/boleto/{nota_fiscal}', [TituloController::class, 'gerarBoleto'])->name('boleto.gerar');
});

// Fallback para rota não encontrada
Route::fallback(function () {
    return view('errors.404');
});