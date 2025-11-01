<?php

use Illuminate\Support\Facades\Route;
use Frede\AiGenerator\Http\Controllers\GeradorToolController; // <-- Namespace corrigido

Route::middleware('web') // <-- Boa prática: aplicar o middleware web
     ->group(function () {
    Route::get('/gerador', [GeradorToolController::class, 'show'])->name('gerador.show');
    Route::post('/gerador', [GeradorToolController::class, 'run'])->name('gerador.run');
});