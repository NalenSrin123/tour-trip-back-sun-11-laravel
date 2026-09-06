<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index(): JsonResponse
    {
        $roles = DB::table('roles')->get();

        return response()->json([
            'success' => true,
            'message' => 'Roles retrieved successfully',
            'data' => $roles,
        ], 200);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'role_name' => 'required|string|max:255|unique:roles,role_name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $id = DB::table('roles')->insertGetId([
            'role_name' => $request->role_name,
            'created_at' => now(),
            'updated_at' => now(),
        ], 'role_id');

        $role = DB::table('roles')->where('role_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Role created successfully',
            'data' => $role,
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show(string $id): JsonResponse
    {
        $role = DB::table('roles')->where('role_id', $id)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Role retrieved successfully',
            'data' => $role,
        ], 200);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $role = DB::table('roles')->where('role_id', $id)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null,
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_name' => 'sometimes|required|string|max:255|unique:roles,role_name,' . $id . ',role_id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::table('roles')
            ->where('role_id', $id)
            ->update([
                'role_name' => $request->role_name,
                'updated_at' => now(),
            ]);

        $updatedRole = DB::table('roles')->where('role_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Role updated successfully',
            'data' => $updatedRole,
        ], 200);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $role = DB::table('roles')->where('role_id', $id)->first();

        if (!$role) {
            return response()->json([
                'success' => false,
                'message' => 'Role not found',
                'data' => null,
            ], 404);
        }

        DB::table('roles')->where('role_id', $id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Role deleted successfully',
            'data' => null,
        ], 200);
    }
}