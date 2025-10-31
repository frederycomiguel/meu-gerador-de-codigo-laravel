<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeradorToolController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/gerador', [GeradorToolController::class, 'show'])->name('gerador.show');
Route::post('/gerador', [GeradorToolController::class, 'run'])->name('gerador.run');