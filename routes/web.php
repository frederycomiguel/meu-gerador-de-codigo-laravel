<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GeradorToolController;

Route::get('/', function () {
    return view('welcome');
});

