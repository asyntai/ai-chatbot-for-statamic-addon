<?php

use Illuminate\Support\Facades\Route;

Route::post('/asyntai/connect-status-proxy', function () {
    // Optional: could proxy if needed; currently unused (connect uses asyntai.com directly)
    return response()->json(['ok' => true]);
});


