<?php


use App\Http\Controllers\Api\DestinationController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TourController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\Api\CategoryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

/*
|--------------------------------------------------------------------------
| Auth API Routes
|--------------------------------------------------------------------------
*/

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
/*
|--------------------------------------------------------------------------
| Tour API Routes
|--------------------------------------------------------------------------
*/
Route::get('/tours', [TourController::class, 'index']);
Route::post('/tours', [TourController::class, 'store']);
Route::get('/tours/{id}', [TourController::class, 'show']);
Route::put('/tours/{id}', [TourController::class, 'update']);
Route::delete('/tours/{id}', [TourController::class, 'destroy']);

/*
|--------------------------------------------------------------------------
| Destination API Routes
|--------------------------------------------------------------------------
*/
Route::get('/destinations', [DestinationController::class, 'index']);
Route::post('/destinations', [DestinationController::class, 'store']);
Route::get('/destinations/{id}', [DestinationController::class, 'show']);
Route::put('/destinations/{id}', [DestinationController::class, 'update']);
Route::delete('/destinations/{id}', [DestinationController::class, 'destroy']);


Route::get('/roles', [RoleController::class, 'index']);
Route::post('/roles', [RoleController::class, 'store']);
Route::get('/roles/{id}', [RoleController::class, 'show']);
Route::put('/roles/{id}', [RoleController::class, 'update']);
Route::delete('/roles/{id}', [RoleController::class, 'destroy']);
Route::put('/guides/{id}', [GuideController::class, 'update']);
Route::delete('/guides/{id}', [GuideController::class, 'destroy']);
Route::apiResource('categories', CategoryController::class);
