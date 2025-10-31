<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::apiResource('posts', App\Http\Controllers\PostController::class);
Route::apiResource('articles', App\Http\Controllers\ArticleController::class);
Route::apiResource('produtos', App\Http\Controllers\ProdutoController::class);
Route::apiResource('testes', App\Http\Controllers\TestModelController::class);