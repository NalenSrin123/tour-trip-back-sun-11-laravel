<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user using Query Builder.
     */
    public function register(Request $request)
    {
        // Ensure default roles exist if roles table is empty
        if (DB::table('roles')->count() === 0) {
            DB::table('roles')->insert([
                ['role_name' => 'Customer', 'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Admin',    'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Guide',    'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'phone'     => 'nullable|string|max:20',
            'password'  => 'required|string|min:8|confirmed',
            'role_id'   => 'nullable|integer|exists:roles,role_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Determine role_id: use provided role_id or fall back to default 'Customer' role
        $roleId = $request->input('role_id');
        if (!$roleId) {
            $defaultRole = DB::table('roles')
                ->whereRaw('LOWER(role_name) = ?', ['customer'])
                ->first();

            if ($defaultRole) {
                $roleId = $defaultRole->role_id;
            } else {
                $firstRole = DB::table('roles')->first();
                if ($firstRole) {
                    $roleId = $firstRole->role_id;
                } else {
                    // Automatically seed default 'Customer' role if roles table is empty
                    $roleId = DB::table('roles')->insertGetId([
                        'role_name'  => 'Customer',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], 'role_id');
                }
            }
        }

        $now = now();

        // Insert new user into DB using Query Builder
        $userId = DB::table('users')->insertGetId([
            'role_id'    => $roleId,
            'full_name'  => $request->full_name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'password'   => Hash::make($request->password),
            'created_at' => $now,
            'updated_at' => $now,
        ], 'id');

        // Retrieve created user using Query Builder with role joined
        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.role_id')
            ->select(
                'users.id',
                'users.role_id',
                'roles.role_name',
                'users.full_name',
                'users.email',
                'users.phone',
                'users.created_at',
                'users.updated_at'
            )
            ->where('users.id', $userId)
            ->first();

        // Generate Sanctum access token
        $userModel = User::find($userId);
        $token = $userModel->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'       => true,
            'message'      => 'User registered successfully',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'data'         => $user
        ], 201);
    }

    /**
     * Login user using Query Builder.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation error',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Retrieve user using Query Builder
        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.role_id')
            ->select(
                'users.id',
                'users.role_id',
                'roles.role_name',
                'users.full_name',
                'users.email',
                'users.phone',
                'users.password',
                'users.created_at',
                'users.updated_at'
            )
            ->where('users.email', $request->email)
            ->first();

        // Check if user exists and verify password hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Invalid login credentials',
                'errors'  => [
                    'email' => ['The provided credentials do not match our records.']
                ]
            ], 401);
        }

        // Hide password from response data
        unset($user->password);

        // Generate Sanctum access token
        $userModel = User::find($user->id);
        $token = $userModel->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'       => true,
            'message'      => 'Login successful',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'data'         => $user
        ], 200);
    }

    /**
     * Logout user (Revoke current token).
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status'  => true,
            'message' => 'Logged out successfully'
        ], 200);
    }

    /**
     * Get authenticated user profile using Query Builder.
     */
    public function me(Request $request)
    {
        $userId = $request->user()->id;

        $user = DB::table('users')
            ->leftJoin('roles', 'users.role_id', '=', 'roles.role_id')
            ->select(
                'users.id',
                'users.role_id',
                'roles.role_name',
                'users.full_name',
                'users.email',
                'users.phone',
                'users.created_at',
                'users.updated_at'
            )
            ->where('users.id', $userId)
            ->first();

        return response()->json([
            'status'  => true,
            'message' => 'User profile retrieved successfully',
            'data'    => $user
        ], 200);
    }
}
