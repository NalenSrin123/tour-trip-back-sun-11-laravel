<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuideController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::put('/guides/{id}', [GuideController::class, 'update']);
Route::delete('/guides/{id}', [GuideController::class, 'destroy']);
