<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    /**
     * Display a listing of the bookings.
     */
    public function index(Request $request)
    {
        $query = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('tour_schedule', 'bookings.schedule_id', '=', 'tour_schedule.schedule_id')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->select(
                'bookings.*',
                'users.full_name as customer_name',
                'users.email as customer_email',
                'tours.title as tour_title',
                'tour_schedule.start_date',
                'tour_schedule.end_date'
            );

        if ($request->filled('status')) {
            $query->where('bookings.status', $request->input('status'));
        }

        if ($request->filled('user_id')) {
            $query->where('bookings.user_id', $request->input('user_id'));
        }

        if ($request->filled('schedule_id')) {
            $query->where('bookings.schedule_id', $request->input('schedule_id'));
        }

        $bookings = $query
            ->orderBy('bookings.booking_id', 'desc')
            ->paginate($request->integer('per_page', 15));

        return response()->json([
            'status' => true,
            'message' => 'Bookings retrieved successfully',
            'data' => $bookings
        ], 200);
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|integer|exists:users,id',
            'schedule_id'  => 'required|integer|exists:tour_schedule,schedule_id',
            'total_amount' => 'required|numeric|min:0',
            'booking_code' => 'nullable|string|max:255|unique:bookings,booking_code',
            'status'       => 'nullable|string|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $bookingCode = !empty($validated['booking_code'])
            ? $validated['booking_code']
            : 'BK-' . strtoupper(Str::random(8));

        $now = now();

        $bookingId = DB::table('bookings')->insertGetId([
            'user_id'      => $validated['user_id'],
            'schedule_id'  => $validated['schedule_id'],
            'booking_code' => $bookingCode,
            'total_amount' => $validated['total_amount'],
            'status'       => $validated['status'] ?? 'pending',
            'created_at'   => $now,
            'updated_at'   => $now,
        ], 'booking_id');

        $booking = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('tour_schedule', 'bookings.schedule_id', '=', 'tour_schedule.schedule_id')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->select(
                'bookings.*',
                'users.full_name as customer_name',
                'users.email as customer_email',
                'tours.title as tour_title',
                'tour_schedule.start_date',
                'tour_schedule.end_date'
            )
            ->where('bookings.booking_id', $bookingId)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Booking created successfully',
            'data' => $booking
        ], 201);
    }

    /**
     * Display the specified booking.
     */
    public function show($id)
    {
        $booking = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('tour_schedule', 'bookings.schedule_id', '=', 'tour_schedule.schedule_id')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->select(
                'bookings.*',
                'users.full_name as customer_name',
                'users.email as customer_email',
                'tours.title as tour_title',
                'tour_schedule.start_date',
                'tour_schedule.end_date'
            )
            ->where('bookings.booking_id', $id)
            ->first();

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Booking retrieved successfully',
            'data' => $booking
        ], 200);
    }

    /**
     * Update the specified booking in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = DB::table('bookings')->where('booking_id', $id)->first();

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'booking_code' => 'sometimes|required|string|max:255|unique:bookings,booking_code,' . $id . ',booking_id',
            'user_id'      => 'sometimes|required|integer|exists:users,id',
            'schedule_id'  => 'sometimes|required|integer|exists:tour_schedule,schedule_id',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'status'       => 'sometimes|required|string|in:pending,confirmed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        if (!empty($validated)) {
            $validated['updated_at'] = now();
            DB::table('bookings')->where('booking_id', $id)->update($validated);
        }

        $updatedBooking = DB::table('bookings')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('tour_schedule', 'bookings.schedule_id', '=', 'tour_schedule.schedule_id')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->select(
                'bookings.*',
                'users.full_name as customer_name',
                'users.email as customer_email',
                'tours.title as tour_title',
                'tour_schedule.start_date',
                'tour_schedule.end_date'
            )
            ->where('bookings.booking_id', $id)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Booking updated successfully',
            'data' => $updatedBooking
        ], 200);
    }

    /**
     * Remove the specified booking from storage.
     */
    public function destroy($id)
    {
        $booking = DB::table('bookings')->where('booking_id', $id)->first();

        if (!$booking) {
            return response()->json([
                'status' => false,
                'message' => 'Booking not found'
            ], 404);
        }

        DB::table('bookings')->where('booking_id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Booking deleted successfully'
        ], 200);
    }
}
