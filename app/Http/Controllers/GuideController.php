<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuideController extends Controller
{
    //Edite Guide
    public function update(Request $request, $id)
    {
        // validate request data
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
        ]);

        //check if the guide exists
        $guide = DB::table('guides')
            ->where('guide_id', $id)
            ->first();
        
        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide not found'
            ], 404);
        }

        //check email belong to another guide
        $emailExists = DB::table('guides')
            ->where('email', $request->email)
            ->where('guide_id', '!=', $id)
            ->exists();

        if ($emailExists) {
            return response()->json([
                'success' => false,
                'message' => 'Email is already used by another guide'
            ], 422);
        }

        //update guide
        DB::table('guides')
            ->where('guide_id', $id)
            ->update([
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'email' => $request->email,
                'updated_at' => now(),
            ]);

        //Get the updated guide
        $updatedGuide = DB::table('guides')
            ->where('guide_id', $id)
            ->first();

        return response()->json([
            'success' => true,
            'message' => 'Guide updated successfully',
            'data' => $updatedGuide
        ], 200);

    }

    //Delete Guide

    public function destroy($id)
    {
        //check if the guide exists
        $guide = DB::table('guides')
            ->where('guide_id', $id)
            ->first();
        
        if (!$guide) {
            return response()->json([
                'success' => false,
                'message' => 'Guide not found'
            ], 404);
        }

        //delete guide
        DB::table('guides')
            ->where('guide_id', $id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Guide deleted successfully'
        ], 200);
    }

}
