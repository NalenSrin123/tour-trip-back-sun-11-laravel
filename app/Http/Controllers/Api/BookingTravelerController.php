<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingTravelerController extends Controller
{
    /**
     * Get all travelers for a specific booking
     */
    public function index($bookingId)
    {
        $booking = DB::table('bookings')->where('booking_id', $bookingId)->first();
        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $travelers = DB::table('booking_travelers')
            ->where('booking_id', $bookingId)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Travelers retrieved successfully',
            'data'    => $travelers
        ], 200);
    }

    /**
     * Store single or multiple travelers for a booking
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'booking_id'            => 'required|integer|exists:bookings,booking_id',
            'travelers'             => 'required|array|min:1',
            'travelers.*.full_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $now = now();
        $travelersData = [];

        foreach ($request->input('travelers') as $traveler) {
            $travelersData[] = [
                'booking_id' => $request->input('booking_id'),
                'full_name'  => $traveler['full_name'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('booking_travelers')->insert($travelersData);

        $insertedTravelers = DB::table('booking_travelers')
            ->where('booking_id', $request->input('booking_id'))
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Travelers added successfully',
            'data'    => $insertedTravelers
        ], 201);
    }

    /**
     * Update a specific traveler
     */
    public function update(Request $request, $id)
    {
        $traveler = DB::table('booking_travelers')->where('traveler_id', $id)->first();

        if (!$traveler) {
            return response()->json([
                'success' => false,
                'message' => 'Traveler not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (!empty($validated)) {
            $validated['updated_at'] = now();
            DB::table('booking_travelers')->where('traveler_id', $id)->update($validated);
        }

        $updatedTraveler = DB::table('booking_travelers')->where('traveler_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Traveler updated successfully',
            'data'    => $updatedTraveler
        ], 200);
    }

    /**
     * Delete a traveler
     */
    public function destroy($id)
    {
        $traveler = DB::table('booking_travelers')->where('traveler_id', $id)->first();

        if (!$traveler) {
            return response()->json([
                'success' => false,
                'message' => 'Traveler not found'
            ], 404);
        }

        DB::table('booking_travelers')->where('traveler_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Traveler deleted successfully'
        ], 200);
    }
}