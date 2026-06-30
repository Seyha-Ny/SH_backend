<?php

use Illuminate\Support\Facades\Route;

Route::get('/swagger', function () {
    return view('swagger');
});

Route::get('/swagger.json', function () {
    return response()->json(require public_path('swagger.json'));
});