<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\{
    AuthenticatedSessionController,
    ConfirmablePasswordController,
    EmailVerificationNotificationController,
    EmailVerificationPromptController,
    NewPasswordController,
    PasswordController,
    PasswordResetLinkController,
    RegisteredUserController,
    VerifyEmailController
};
use App\Http\Controllers\NotaController;

/*
|--------------------------------------------------------------------------
| ROTAS DE USUÁRIO NÃO AUTENTICADO
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

/*
|--------------------------------------------------------------------------
| ROTAS AUTENTICADAS GERAIS
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Verificação de e-mail
    Route::get('verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');
    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Confirmação de senha e logout
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])->name('password.confirm');
    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);
    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

/*
|--------------------------------------------------------------------------
| ROTAS DE CONTAS (acesso a criação de notas)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:contas'])->prefix('contas')->name('contas.')->group(function () {
    Route::get('notas', [NotaController::class, 'index'])->name('notas.index');
    Route::get('notas/create', [NotaController::class, 'create'])->name('notas.create');
    Route::post('notas', [NotaController::class, 'store'])->name('notas.store');
    Route::get('notas/{nota}/edit', [NotaController::class, 'edit'])->name('notas.edit');
    Route::put('notas/{nota}', [NotaController::class, 'update'])->name('notas.update');
    Route::delete('notas/{nota}', [NotaController::class, 'destroy'])->name('notas.destroy');
});

/*
|--------------------------------------------------------------------------
| ROTAS DA CHEFIA (aprovação de notas)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:chefia'])->prefix('chefia')->name('chefia.')->group(function () {
    Route::post('notas/{nota}/aprovar', [NotaController::class, 'aprovar'])->name('notas.aprovar');
    Route::post('notas/{nota}/rejeitar', [NotaController::class, 'rejeitar'])->name('notas.rejeitar');
    Route::get('notas/{nota}/detalhes', [NotaController::class, 'detalhes'])->name('notas.detalhes');
});

/*
|--------------------------------------------------------------------------
| ROTAS DO FINANCEIRO (aceitação e comprovantes)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:financeiro'])->prefix('financeiro')->name('financeiro.')->group(function () {
    Route::post('notas/{nota}/aceitar', [NotaController::class, 'aceitar'])->name('notas.aceitar');
    Route::post('notas/{nota}/recusar', [NotaController::class, 'recusar'])->name('notas.recusar');

    // Apenas financeiro acessa comprovante final
    Route::get('notas/{nota}/comprovante', [NotaController::class, 'showComprovante'])->name('notas.comprovante');
});
