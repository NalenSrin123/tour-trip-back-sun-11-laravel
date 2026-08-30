<?php

use App\Http\Controllers\GuideController;
use Illuminate\Support\Facades\Route;

Route::apiResource('guides', GuideController::class);
