<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    // GET /api/customers
    public function index(Request $request)
    {
        $customerRoleId = DB::table('roles')
            ->whereRaw('LOWER(role_name) = ?', ['customer'])
            ->value('role_id');

        $customers = User::query()
            ->when($customerRoleId, fn ($q) => $q->where('role_id', $customerRoleId))
            ->when($request->search, function ($q) use ($request) {
                $q->where('full_name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($customers);
    }

    // POST /api/customers
    public function store(StoreCustomerRequest $request)
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

        $customer = User::create([
            'full_name' => $request->name ?? $request->full_name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'phone'     => $request->phone,
            'role_id'   => $customerRoleId,
        ]);

        return response()->json([
            'message' => 'Customer created successfully',
            'data'    => $customer,
        ], 201);
    }
}
