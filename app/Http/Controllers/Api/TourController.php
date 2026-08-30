<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TourController extends Controller
{
    /**
     * Display a listing of the tours.
     */
    public function index(Request $request)
    {
        $tours = DB::table('tours')
            ->leftJoin('categories', 'tours.category_id', '=', 'categories.category_id')
            ->leftJoin('destinations', 'tours.destination_id', '=', 'destinations.destination_id')
            ->select(
                'tours.*',
                'categories.category_name',
                'destinations.destination_name'
            )
            ->orderBy('tours.tour_id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Tours retrieved successfully',
            'data' => $tours
        ], 200);
    }

    /**
     * Store a newly created tour in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'category_id' => 'required|integer|exists:categories,category_id',
            'destination_id' => 'required|integer|exists:destinations,destination_id',
            'title' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'duration_days' => 'required|integer|min:1',
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

        $tourId = DB::table('tours')->insertGetId([
            'category_id' => $validated['category_id'],
            'destination_id' => $validated['destination_id'],
            'title' => $validated['title'],
            'base_price' => $validated['base_price'],
            'duration_days' => $validated['duration_days'],
            'created_at' => $now,
            'updated_at' => $now,
        ], 'tour_id');

        $tour = DB::table('tours')
            ->leftJoin('categories', 'tours.category_id', '=', 'categories.category_id')
            ->leftJoin('destinations', 'tours.destination_id', '=', 'destinations.destination_id')
            ->select(
                'tours.*',
                'categories.category_name',
                'destinations.destination_name'
            )
            ->where('tours.tour_id', $tourId)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Tour created successfully',
            'data' => $tour
        ], 201);
    }

    /**
     * Display the specified tour.
     */
    public function show($id)
    {
        $tour = DB::table('tours')
            ->leftJoin('categories', 'tours.category_id', '=', 'categories.category_id')
            ->leftJoin('destinations', 'tours.destination_id', '=', 'destinations.destination_id')
            ->select(
                'tours.*',
                'categories.category_name',
                'destinations.destination_name'
            )
            ->where('tours.tour_id', $id)
            ->first();

        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Tour retrieved successfully',
            'data' => $tour
        ], 200);
    }

    /**
     * Update the specified tour in storage.
     */
    public function update(Request $request, $id)
    {
        $tour = DB::table('tours')->where('tour_id', $id)->first();

        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'category_id' => 'sometimes|required|integer|exists:categories,category_id',
            'destination_id' => 'sometimes|required|integer|exists:destinations,destination_id',
            'title' => 'sometimes|required|string|max:255',
            'base_price' => 'sometimes|required|numeric|min:0',
            'duration_days' => 'sometimes|required|integer|min:1',
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
            DB::table('tours')->where('tour_id', $id)->update($validated);
        }

        $updatedTour = DB::table('tours')
            ->leftJoin('categories', 'tours.category_id', '=', 'categories.category_id')
            ->leftJoin('destinations', 'tours.destination_id', '=', 'destinations.destination_id')
            ->select(
                'tours.*',
                'categories.category_name',
                'destinations.destination_name'
            )
            ->where('tours.tour_id', $id)
            ->first();

        return response()->json([
            'status' => true,
            'message' => 'Tour updated successfully',
            'data' => $updatedTour
        ], 200);
    }

    /**
     * Remove the specified tour from storage.
     */
    public function destroy($id)
    {
        $tour = DB::table('tours')->where('tour_id', $id)->first();

        if (!$tour) {
            return response()->json([
                'status' => false,
                'message' => 'Tour not found'
            ], 404);
        }

        DB::table('tours')->where('tour_id', $id)->delete();

        return response()->json([
            'status' => true,
            'message' => 'Tour deleted successfully'
        ], 200);
    }
}
