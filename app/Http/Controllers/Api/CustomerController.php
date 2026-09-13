<?php
// app/Http/Controllers/Api/CustomerController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCustomerRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class CustomerController extends Controller
{
    // GET /api/customers
    public function index(Request $request)
    {
        $customerRoleId = Role::where('name', 'customer')->value('id');

        $customers = User::query()
            ->when($customerRoleId, fn ($q) => $q->where('role_id', $customerRoleId))
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($customers);
    }

    // POST /api/customers
    // app/Http/Controllers/Api/CustomerController.php

public function store(StoreCustomerRequest $request)
{
    $customerRoleId = Role::where('name', 'customer')->value('id');

    $customer = User::create([
        'full_name' => $request->name,      // map incoming 'name' to DB column 'full_name'
        'email'     => $request->email,
        'password'  => Hash::make($request->password),
        'phone'     => $request->phone,
        'role_id'   => $customerRoleId,
    ]);

    return response()->json([
        'message' => 'Customer created successfully',
        'data'    => $customer,
    ], 201);
}protected $fillable = [
    'full_name', 'email', 'password', 'role_id', 'phone', 'address',
];
}
