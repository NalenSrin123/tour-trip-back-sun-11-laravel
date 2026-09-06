<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TourScheduleController extends Controller
{
    /**
     * Display a listing of the tour schedules.
     */
    public function index(Request $request)
    {
        $query = DB::table('tour_schedule')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->leftJoin('guides', 'tour_schedule.guide_id', '=', 'guides.guide_id')
            ->select(
                'tour_schedule.*',
                'tours.title as tour_title',
                'guides.full_name as guide_name',
                'guides.email as guide_email'
            );

        if ($request->filled('tour_id')) {
            $query->where('tour_schedule.tour_id', $request->input('tour_id'));
        }

        if ($request->filled('guide_id')) {
            $query->where('tour_schedule.guide_id', $request->input('guide_id'));
        }

        if ($request->filled('status')) {
            $query->where('tour_schedule.status', $request->input('status'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('tour_schedule.start_date', '>=', $request->input('start_date'));
        }

        $schedules = $query->orderBy('tour_schedule.schedule_id', 'desc')->get();

        return response()->json([
            'status' => true,
            'message' => 'Tour schedules retrieved successfully',
            'data' => $schedules
        ], 200);
    }

    /**
     * Store a newly created tour schedule in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|integer|exists:tours,tour_id',
            'guide_id' => 'required|integer|exists:guides,guide_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'booked_seats' => 'nullable|integer|min:0|lte:max_capacity',
            'status' => 'nullable|string|in:UPCOMING,COMPLETED',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $now = now();

        $scheduleId = DB::table('tour_schedule')->insertGetId([
            'tour_id' => $validated['tour_id'],
            'guide_id' => $validated['guide_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'max_capacity' => $validated['max_capacity'],
            'booked_seats' => $validated['booked_seats'] ?? 0,
            'status' => $validated['status'] ?? 'UPCOMING',
            'created_at' => $now,
            'updated_at' => $now,
        ], 'schedule_id');

        $schedule = DB::table('tour_schedule')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->leftJoin('guides', 'tour_schedule.guide_id', '=', 'guides.guide_id')
            ->select(
                'tour_schedule.*',
                'tours.title as tour_title',
                'guides.full_name as guide_name',
                'guides.email as guide_email'
            )
            ->where('tour_schedule.schedule_id', $scheduleId)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Tour schedule created successfully',
            'data' => $schedule
        ], 201);
    }

    /**
     * Display the specified tour schedule.
     */
    public function show($id)
    {
        $schedule = DB::table('tour_schedule')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->leftJoin('guides', 'tour_schedule.guide_id', '=', 'guides.guide_id')
            ->select(
                'tour_schedule.*',
                'tours.title as tour_title',
                'guides.full_name as guide_name',
                'guides.email as guide_email'
            )
            ->where('tour_schedule.schedule_id', $id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Tour schedule not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tour schedule retrieved successfully',
            'data' => $schedule
        ], 200);
    }

    /**
     * Update the specified tour schedule in storage.
     */
    public function update(Request $request, $id)
    {
        $schedule = DB::table('tour_schedule')
            ->where('schedule_id', $id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Tour schedule not found'
            ], 404);
        }

        $startDate = $request->input('start_date');

        $validator = Validator::make($request->all(), [
            'tour_id' => 'sometimes|required|integer|exists:tours,tour_id',
            'guide_id' => 'sometimes|required|integer|exists:guides,guide_id',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date' . ($startDate ? '|after_or_equal:start_date' : ''),
            'max_capacity' => 'sometimes|required|integer|min:1',
            'booked_seats' => 'sometimes|required|integer|min:0',
            'status' => 'sometimes|required|string|in:UPCOMING,COMPLETED',
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
            DB::table('tour_schedule')
                ->where('schedule_id', $id)
                ->update($validated);
        }

        $updatedSchedule = DB::table('tour_schedule')
            ->leftJoin('tours', 'tour_schedule.tour_id', '=', 'tours.tour_id')
            ->leftJoin('guides', 'tour_schedule.guide_id', '=', 'guides.guide_id')
            ->select(
                'tour_schedule.*',
                'tours.title as tour_title',
                'guides.full_name as guide_name',
                'guides.email as guide_email'
            )
            ->where('tour_schedule.schedule_id', $id)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Tour schedule updated successfully',
            'data' => $updatedSchedule
        ], 200);
    }

    /**
     * Remove the specified tour schedule from storage.
     */
    public function destroy($id)
    {
        $schedule = DB::table('tour_schedule')
            ->where('schedule_id', $id)
            ->first();

        if (!$schedule) {
            return response()->json([
                'status' => false,
                'message' => 'Tour schedule not found'
            ], 404);
        }

        DB::table('tour_schedule')
            ->where('schedule_id', $id)
            ->delete();

        return response()->json([
            'status' => true,
            'message' => 'Tour schedule deleted successfully'
        ], 200);
    }
}
