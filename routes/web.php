<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'name' => 'Appointment Booking API',
    'version' => '1.0.0',
    'docs' => '/docs/openapi.yaml',
]));

Route::view('/demo', 'demo')->name('demo');
