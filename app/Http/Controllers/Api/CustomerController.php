<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Helper method to retrieve or auto-create Customer role ID.
     */
    private function getCustomerRoleId(): int
    {
        $customerRoleId = DB::table('roles')
            ->whereRaw('LOWER(role_name) = ?', ['customer'])
            ->value('role_id');

        if (!$customerRoleId) {
            $customerRoleId = DB::table('roles')->insertGetId([
                'role_name'  => 'Customer',
                'created_at' => now(),
                'updated_at' => now(),
            ], 'role_id');
        }

        return $customerRoleId;
    }

    /**
     * GET /api/customers
     * Display a listing of customers with optional search & pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $customerRoleId = $this->getCustomerRoleId();

        $query = User::query()
            ->with('role')
            ->where('role_id', $customerRoleId)
            ->when($request->filled('name'), function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->name}%");
            })
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    if (is_numeric($request->search)) {
                        $sub->where('id', $request->search);
                    }
                    $sub->orWhere('full_name', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%")
                        ->orWhere('phone', 'like', "%{$request->search}%");
                });
            });



        $customers = $query->latest()->get();

        return response()->json([
            'status'  => true,
            'message' => 'Customers retrieved successfully',
            'data'    => $customers
        ], 200);
    }


    /**
     * POST /api/customers
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customerRoleId = $this->getCustomerRoleId();

        $fullName = $request->input('full_name') ?? $request->input('name');
        if (empty($fullName)) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => [
                    'full_name' => ['The full_name or name field is required.']
                ]
            ], 422);
        }

        $customer = User::create([
            'full_name' => $fullName,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'role_id'   => $customerRoleId,
        ]);

        $customer->load('role');

        return response()->json([
            'status'  => true,
            'message' => 'Customer created successfully',
            'data'    => $customer,
        ], 201);
    }

    /**
     * GET /api/customers/{id}
     * Display the specified customer.
     */
    public function show(string $id): JsonResponse
    {
        $customerRoleId = $this->getCustomerRoleId();

        $customer = User::with('role')
            ->where('role_id', $customerRoleId)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer not found',
            ], 404);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Customer retrieved successfully',
            'data'    => $customer,
        ], 200);
    }

    /**
     * PUT/PATCH /api/customers/{id}
     * Update the specified customer.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $customerRoleId = $this->getCustomerRoleId();

        $customer = User::where('role_id', $customerRoleId)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'      => 'sometimes|nullable|string|max:255',
            'full_name' => 'sometimes|nullable|string|max:255',
            'email'     => ['sometimes', 'required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'password'  => 'sometimes|nullable|string|min:6',
            'phone'     => 'sometimes|nullable|string|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        $fullName = $request->input('full_name') ?? $request->input('name') ?? $customer->full_name;

        $updateData = [
            'full_name' => $fullName,
        ];

        if ($request->has('email')) {
            $updateData['email'] = $request->input('email');
        }

        if ($request->has('phone')) {
            $updateData['phone'] = $request->input('phone');
        }

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->input('password'));
        }

        $customer->update($updateData);
        $customer->load('role');

        return response()->json([
            'status'  => true,
            'message' => 'Customer updated successfully',
            'data'    => $customer,
        ], 200);
    }

    /**
     * DELETE /api/customers/{id}
     * Remove the specified customer from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $customerRoleId = $this->getCustomerRoleId();

        $customer = User::where('role_id', $customerRoleId)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'status'  => false,
                'message' => 'Customer not found',
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Customer deleted successfully',
        ], 200);
    }
}
