<?php

use App\Http\Controllers\NovelParserController;
use Illuminate\Support\Facades\Route;

Route::post('/parse', [NovelParserController::class, 'parse']);
