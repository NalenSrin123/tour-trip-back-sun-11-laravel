    <?php


    use App\Http\Controllers\Api\DestinationController;
    use App\Http\Controllers\Api\RoleController;
    use App\Http\Controllers\Api\TourController;
    use App\Http\Controllers\Api\AuthController;
    use App\Http\Controllers\Api\BookingController;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\GuideController;
    use App\Http\Controllers\Api\CategoryController;
    use App\Http\Controllers\Api\TourScheduleController;
    use App\Http\Controllers\Api\CustomerController;
    use App\Http\Controllers\Api\PaymentController;
    use App\Http\Controllers\Api\BookingTravelerController;
    use App\Http\Controllers\Api\ReviewController;

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

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
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

    /*
    |--------------------------------------------------------------------------
    | Tour Schedule API Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/tour-schedules', [TourScheduleController::class, 'index']);
    Route::post('/tour-schedules', [TourScheduleController::class, 'store']);
    Route::get('/tour-schedules/{id}', [TourScheduleController::class, 'show']);
    Route::put('/tour-schedules/{id}', [TourScheduleController::class, 'update']);
    Route::delete('/tour-schedules/{id}', [TourScheduleController::class, 'destroy']);

    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings', [BookingController::class, 'index']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);
    Route::apiResource('guides', GuideController::class);


    /*
    |--------------------------------------------------------------------------
    | Customer API Routes
    |--------------------------------------------------------------------------
    */
    Route::apiResource('customers', CustomerController::class);
    /*
     /*
    |--------------------------------------------------------------------------
    | Payment API Routes
    |--------------------------------------------------------------------------
    */


    Route::get('/payments', [PaymentController::class, 'index']);
    Route::post('/payments', [PaymentController::class, 'store']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::put('/payments/{id}', [PaymentController::class, 'update']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);


    /*
    |--------------------------------------------------------------------------
    | Booking traveler API Routes
    |--------------------------------------------------------------------------
    */
    Route::get('/booking-travelers/{bookingId}', [BookingTravelerController::class, 'index']);
    Route::post('/booking-travelers', [BookingTravelerController::class, 'store']);
    Route::put('/booking-travelers/{id}', [BookingTravelerController::class, 'update']);
    Route::delete('/booking-travelers/{id}', [BookingTravelerController::class, 'destroy']);


    // Public / Read routes
    Route::get('/reviews', [ReviewController::class, 'index']);
    Route::get('/reviews/{id}', [ReviewController::class, 'show']);
    // Protected / Write routes (add auth middleware if required)
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
