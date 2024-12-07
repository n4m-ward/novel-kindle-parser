<?php

use App\Http\Controllers\NovelParserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/parse', [NovelParserController::class, 'parse']);
