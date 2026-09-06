<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * Update the specified booking.
     */
    public function update(Request $request, string $id): JsonResponse
    {
       
        $booking = DB::table('bookings')->where('booking_id', $id)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'data' => null,
            ], 404);
        }

        
        $validator = Validator::make($request->all(), [
            'booking_code' => 'sometimes|required|string|max:255|unique:bookings,booking_code,' . $id . ',booking_id',
            'user_id'      => 'sometimes|required|integer',
            'schedule_id'  => 'sometimes|required|integer',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status'       => 'sometimes|required|string|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

      
        $dataToUpdate = $validator->validated();
        $dataToUpdate['updated_at'] = now();

        DB::table('bookings')
            ->where('booking_id', $id)
            ->update($dataToUpdate);

        $updatedBooking = DB::table('bookings')->where('booking_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'data'    => $updatedBooking,
        ], 200);
    }

    /**
     * Remove the specified booking.
     */
    public function destroy(string $id): JsonResponse
    {
       
        $booking = DB::table('bookings')->where('booking_id', $id)->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found',
                'data' => null,
            ], 404);
        }

       
        DB::table('bookings')->where('booking_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking deleted successfully',
            'data'    => null,
        ], 200);
    }
}