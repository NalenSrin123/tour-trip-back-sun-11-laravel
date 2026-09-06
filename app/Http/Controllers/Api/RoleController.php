<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = DB::table('roles')->get();

        return response()->json([
            'status' => 'success',
            'data' => $roles
        ], 200);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name',
        ]);

        $roleId = DB::table('roles')->insertGetId([
            'role_name' => $request->role_name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $role = DB::table('roles')->where('role_id', $roleId)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Role created successfully',
            'data' => $role
        ], 201);
    }

    /**
     * Display the specified role.
     */
    public function show($id)
    {
        $role = DB::table('roles')->where('role_id', $id)->first();

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $role
        ], 200);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, $id)
    {
        $role = DB::table('roles')->where('role_id', $id)->first();

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found'
            ], 404);
        }

        $request->validate([
            'role_name' => 'required|string|max:255|unique:roles,role_name,' . $id . ',role_id',
        ]);

        DB::table('roles')
            ->where('role_id', $id)
            ->update([
                'role_name' => $request->role_name,
                'updated_at' => now(),
            ]);

        $updatedRole = DB::table('roles')->where('role_id', $id)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Role updated successfully',
            'data' => $updatedRole
        ], 200);
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy($id)
    {
        $role = DB::table('roles')->where('role_id', $id)->first();

        if (!$role) {
            return response()->json([
                'status' => 'error',
                'message' => 'Role not found'
            ], 404);
        }

        DB::table('roles')->where('role_id', $id)->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Role deleted successfully'
        ], 200);
    }
}