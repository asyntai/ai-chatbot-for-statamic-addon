<?php

use Illuminate\Support\Facades\Route;

// In CP route context, Statamic applies CP prefix and auth middleware automatically.
Route::name('asyntai.')->prefix('asyntai')->group(function () {
    Route::get('/', [\Asyntai\Statamic\Chatbot\Http\Controllers\Cp\AsyntaiController::class, 'index'])->name('index');
    Route::post('save', [\Asyntai\Statamic\Chatbot\Http\Controllers\Cp\AsyntaiController::class, 'save'])->name('save');
    Route::post('reset', [\Asyntai\Statamic\Chatbot\Http\Controllers\Cp\AsyntaiController::class, 'reset'])->name('reset');
});


