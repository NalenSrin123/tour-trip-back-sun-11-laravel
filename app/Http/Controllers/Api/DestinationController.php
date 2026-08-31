<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DestinationController extends Controller
{
    /**
     * Display a listing of destinations.
     */
    public function index(): JsonResponse
    {
        $destinations = DB::table('destinations')->get();

        return response()->json([
            'success' => true,
            'message' => 'Destinations retrieved successfully',
            'data' => $destinations,
        ], 200);
    }

    /**
     * Store a newly created destination.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'destination_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id = DB::table('destinations')->insertGetId([
            'destination_name' => $request->destination_name,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'destination_id');

        $destination = DB::table('destinations')->where('destination_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Destination created successfully',
            'data' => $destination,
        ], 201);
    }

    /**
     * Display the specified destination.
     */
    public function show(string $id): JsonResponse
    {
        $destination = DB::table('destinations')->where('destination_id', $id)->first();

        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Destination retrieved successfully',
            'data' => $destination,
        ], 200);
    }

    /**
     * Update the specified destination.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $destination = DB::table('destinations')->where('destination_id', $id)->first();

        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'destination_name' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('destinations')
            ->where('destination_id', $id)
            ->update([
                'destination_name' => $request->destination_name,
                'updated_at' => now(),
            ]);

        $updated = DB::table('destinations')->where('destination_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Destination updated successfully',
            'data' => $updated,
        ], 200);
    }

    /**
     * Remove the specified destination.
     */
    public function destroy(string $id): JsonResponse
    {
        $destination = DB::table('destinations')->where('destination_id', $id)->first();

        if (!$destination) {
            return response()->json([
                'success' => false,
                'message' => 'Destination not found',
            ], 404);
        }

        DB::table('destinations')->where('destination_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Destination deleted successfully',
        ], 200);
    }
}