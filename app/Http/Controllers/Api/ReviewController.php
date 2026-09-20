<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews with joined relations.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $reviews = DB::table('reviews')
                ->join('users', 'reviews.user_id', '=', 'users.id')
                ->join('tours', 'reviews.tour_id', '=', 'tours.tour_id')
                ->leftJoin('bookings', 'reviews.booking_id', '=', 'bookings.booking_id')
                ->select([
                    'reviews.review_id',
                    'reviews.tour_id',
                    'reviews.user_id',
                    'reviews.booking_id',
                    'reviews.rating',
                    'reviews.comment',
                    'reviews.is_approved',
                    'reviews.created_at',
                    'reviews.updated_at',
                    'users.full_name as user_name',
                    'users.email as user_email',
                    'tours.title as tour_title'
                ])
                ->when($request->filled('tour_id'), function ($query) use ($request) {
                    return $query->where('reviews.tour_id', $request->tour_id);
                })
                ->when($request->filled('is_approved'), function ($query) use ($request) {
                    return $query->where('reviews.is_approved', $request->boolean('is_approved'));
                })
                ->orderBy('reviews.created_at', 'desc')
                ->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Reviews retrieved successfully.',
                'data'    => $reviews
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve reviews.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created review.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'tour_id'     => 'required|integer|exists:tours,tour_id',
                'user_id'     => 'required|integer|exists:users,id',
                'booking_id'  => 'nullable|integer|exists:bookings,booking_id',
                'rating'      => 'required|integer|min:1|max:5',
                'comment'     => 'nullable|string',
                'is_approved' => 'boolean',
            ]);

            $now = now();

            // Explicitly pass 'review_id' so PostgreSQL returns the correct primary key
            $reviewId = DB::table('reviews')->insertGetId([
                'tour_id'     => $validated['tour_id'],
                'user_id'     => $validated['user_id'],
                'booking_id'  => $validated['booking_id'] ?? null,
                'rating'      => $validated['rating'],
                'comment'     => $validated['comment'] ?? null,
                'is_approved' => $validated['is_approved'] ?? false,
                'created_at'  => $now,
                'updated_at'  => $now,
            ], 'review_id');

            $review = DB::table('reviews')->where('review_id', $reviewId)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'Review submitted successfully.',
                'data'    => $review
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to create review.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified review.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $review = DB::table('reviews')
                ->join('users', 'reviews.user_id', '=', 'users.id')
                ->join('tours', 'reviews.tour_id', '=', 'tours.tour_id')
                ->leftJoin('bookings', 'reviews.booking_id', '=', 'bookings.booking_id')
                ->select([
                    'reviews.*',
                    'users.full_name as user_name',
                    'users.email as user_email',
                    'tours.title as tour_title'
                ])
                ->where('reviews.review_id', $id)
                ->first();

            if (!$review) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Review with ID {$id} not found."
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Review details retrieved successfully.',
                'data'    => $review
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve review.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified review.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $review = DB::table('reviews')->where('review_id', $id)->first();

            if (!$review) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Review with ID {$id} not found."
                ], 404);
            }

            $validated = $request->validate([
                'rating'      => 'sometimes|integer|min:1|max:5',
                'comment'     => 'nullable|string',
                'is_approved' => 'sometimes|boolean',
            ]);

            $validated['updated_at'] = now();

            DB::table('reviews')
                ->where('review_id', $id)
                ->update($validated);

            $updatedReview = DB::table('reviews')->where('review_id', $id)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'Review updated successfully.',
                'data'    => $updatedReview
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Validation failed.',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to update review.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified review.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = DB::table('reviews')->where('review_id', $id)->delete();

            if (!$deleted) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Review with ID {$id} not found or already deleted."
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Review deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete review.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}