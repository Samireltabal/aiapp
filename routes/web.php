<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// AI API Routes
Route::post('/ai/query', [HomeController::class, 'query'])->name('ai.query');
Route::post('/ai/explain-code', [HomeController::class, 'explainCode'])->name('ai.explain-code');
Route::post('/ai/summarize', [HomeController::class, 'summarize'])->name('ai.summarize');
Route::post('/ai/ask', [HomeController::class, 'ask'])->name('ai.ask');
