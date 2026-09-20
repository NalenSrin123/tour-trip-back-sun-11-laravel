<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Payment methods accepted by the gateway integration.
     */
    private const METHODS = ['ABA', 'Card', 'Cash'];

    /**
     * Lifecycle states a payment can be in.
     */
    private const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    /**
     * Display a listing of payments with their booking context.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $payments = DB::table('payments')
                ->join('bookings', 'payments.booking_id', '=', 'bookings.booking_id')
                ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
                ->select([
                    'payments.payment_id',
                    'payments.booking_id',
                    'payments.transaction_ref',
                    'payments.payment_method',
                    'payments.amount',
                    'payments.status',
                    'payments.paid_at',
                    'payments.created_at',
                    'payments.updated_at',
                    'bookings.booking_code',
                    'bookings.total_amount as booking_total',
                    'users.full_name as customer_name',
                    'users.email as customer_email',
                ])
                ->when($request->filled('booking_id'), function ($query) use ($request) {
                    return $query->where('payments.booking_id', $request->input('booking_id'));
                })
                ->when($request->filled('status'), function ($query) use ($request) {
                    return $query->where('payments.status', $request->input('status'));
                })
                ->when($request->filled('payment_method'), function ($query) use ($request) {
                    return $query->where('payments.payment_method', $request->input('payment_method'));
                })
                ->orderBy('payments.payment_id', 'desc')
                ->get();

            return response()->json([
                'status'  => 'success',
                'message' => 'Payments retrieved successfully.',
                'data'    => $payments
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve payments.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payment.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'booking_id'      => 'required|integer|exists:bookings,booking_id',
                'transaction_ref' => 'required|string|max:255|unique:payments,transaction_ref',
                'payment_method'  => 'required|in:' . implode(',', self::METHODS),
                'amount'          => 'required|numeric|min:0',
                'status'          => 'sometimes|in:' . implode(',', self::STATUSES),
                'paid_at'         => 'nullable|date',
            ]);

            $now    = now();
            $status = $validated['status'] ?? 'pending';

            // Only a settled payment carries a paid_at timestamp.
            $paidAt = $validated['paid_at'] ?? ($status === 'paid' ? $now : null);

            // Explicitly pass 'payment_id' so PostgreSQL returns the correct primary key
            $paymentId = DB::table('payments')->insertGetId([
                'booking_id'      => $validated['booking_id'],
                'transaction_ref' => $validated['transaction_ref'],
                'payment_method'  => $validated['payment_method'],
                'amount'          => $validated['amount'],
                'status'          => $status,
                'paid_at'         => $paidAt,
                'created_at'      => $now,
                'updated_at'      => $now,
            ], 'payment_id');

            $payment = DB::table('payments')->where('payment_id', $paymentId)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment created successfully.',
                'data'    => $payment
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
                'message' => 'Failed to create payment.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $payment = DB::table('payments')
                ->join('bookings', 'payments.booking_id', '=', 'bookings.booking_id')
                ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
                ->select([
                    'payments.*',
                    'bookings.booking_code',
                    'bookings.total_amount as booking_total',
                    'users.full_name as customer_name',
                    'users.email as customer_email',
                ])
                ->where('payments.payment_id', $id)
                ->first();

            if (!$payment) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Payment with ID {$id} not found."
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment details retrieved successfully.',
                'data'    => $payment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to retrieve payment.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified payment.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $payment = DB::table('payments')->where('payment_id', $id)->first();

            if (!$payment) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Payment with ID {$id} not found."
                ], 404);
            }

            $validated = $request->validate([
                'booking_id'      => 'sometimes|integer|exists:bookings,booking_id',
                'transaction_ref' => 'sometimes|string|max:255|unique:payments,transaction_ref,' . $id . ',payment_id',
                'payment_method'  => 'sometimes|in:' . implode(',', self::METHODS),
                'amount'          => 'sometimes|numeric|min:0',
                'status'          => 'sometimes|in:' . implode(',', self::STATUSES),
                'paid_at'         => 'sometimes|nullable|date',
            ]);

            if (empty($validated)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'No valid fields supplied to update.'
                ], 422);
            }

            // Stamp paid_at the first time a payment settles, and clear it if it un-settles.
            if (array_key_exists('status', $validated) && !array_key_exists('paid_at', $validated)) {
                if ($validated['status'] === 'paid' && is_null($payment->paid_at)) {
                    $validated['paid_at'] = now();
                } elseif ($validated['status'] !== 'paid') {
                    $validated['paid_at'] = null;
                }
            }

            $validated['updated_at'] = now();

            DB::table('payments')->where('payment_id', $id)->update($validated);

            $updatedPayment = DB::table('payments')->where('payment_id', $id)->first();

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment updated successfully.',
                'data'    => $updatedPayment
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
                'message' => 'Failed to update payment.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified payment.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = DB::table('payments')->where('payment_id', $id)->delete();

            if (!$deleted) {
                return response()->json([
                    'status'  => 'error',
                    'message' => "Payment with ID {$id} not found or already deleted."
                ], 404);
            }

            return response()->json([
                'status'  => 'success',
                'message' => 'Payment deleted successfully.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to delete payment.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
