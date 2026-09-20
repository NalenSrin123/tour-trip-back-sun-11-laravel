<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
class PaymentController extends Controller
{
    /**
     * Get all payments
     */
    public function index()
    {
        $query = DB::table('payment');
        $payments = $query->get();

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }

    /**
     * Create a new payment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'transaction_ref' => 'required|string|max:255',
            'payment_method' => 'required|in:ABA,Card,Cash',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|max:50',
            'paid_at' => 'nullable|date',
        ]);

        $query = DB::table('payment');
        
        $payment_id = $query->insertGetId([
            'booking_id' => $validated['booking_id'],
            'transaction_ref' => $validated['transaction_ref'],
            'payment_method' => $validated['payment_method'],
            'amount' => $validated['amount'],
            'status' => $validated['status'],
            'paid_at' => $validated['paid_at'] ?? Carbon::now(),
        ], 'payment_id'); 

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully.',
            'data' => ['payment_id' => $payment_id]
        ], 201);
    }

    /**
     * Get a specific payment
     */
    public function show($id)
    {
        $query = DB::table('payment');
        $payment = $query->where('payment_id', $id)->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    /**
     * Update an existing payment
     */
    public function update(Request $request, $id)
    {
        // First check if the record exists
        $checkQuery = DB::table('payment');
        if (!$checkQuery->where('payment_id', $id)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found.'
            ], 404);
        }

        $validated = $request->validate([
            'booking_id' => 'sometimes|required|integer',
            'transaction_ref' => 'sometimes|required|string|max:255',
            'payment_method' => 'sometimes|required|in:ABA,Card,Cash',
            'amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|max:50',
            'paid_at' => 'sometimes|nullable|date',
        ]);

        $query = DB::table('payment');
        $query->where('payment_id', $id)->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully.'
        ]);
    }

    /**
     * Delete a payment
     */
    public function destroy($id)
    {
        $query = DB::table('payment');
        $deleted = $query->where('payment_id', $id)->delete();

        if (!$deleted) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found or already deleted.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully.'
        ]);
    }
}
