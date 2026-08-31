<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DestinationController;

Route::get('/destinations', [DestinationController::class, 'index']);
Route::post('/destinations', [DestinationController::class, 'store']);
Route::get('/destinations/{id}', [DestinationController::class, 'show']);
Route::put('/destinations/{id}', [DestinationController::class, 'update']);
Route::delete('/destinations/{id}', [DestinationController::class, 'destroy']);