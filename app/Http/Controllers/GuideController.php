<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Http\Resources\GuideResource;

class GuideController extends Controller
{
  // Get all guides or list of guides
    public function index()
    {
        $guides = Guide::latest()->get();
        return GuideResource::collection($guides);
    }

    // Create a new Guide
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => 'required|email|unique:guides,email',
            'password'  => 'required|string|min:6',
        ]);

        $guide = Guide::create([
            'full_name'     => $validated['full_name'],
            'phone'         => $validated['phone'] ?? null,
            'email'         => $validated['email'],
            'password_hash' => $validated['password'],
        ]);

        return new GuideResource($guide);
    }

    // get a specific Guide
    // public function show(string $id)
    // {
    //     $guide = Guide::findOrFail($id);
    //     return new GuideResource($guide);
    // }

    // Edit a Guide
    public function update(Request $request, string $id)
    {
        $guide = Guide::findOrFail($id);

        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'phone'     => 'nullable|string|max:20',
            'email'     => ['sometimes', 'email', Rule::unique('guides', 'email')->ignore($guide->guide_id, 'guide_id')],
            'password'  => 'nullable|string|min:6',
        ]);

        if (isset($validated['password'])) {
            $validated['password_hash'] = $validated['password'];
            unset($validated['password']);
        }

        $guide->update($validated);

        return new GuideResource($guide);
    }

    // Delete a Guide
    public function destroy(string $id)
    {
        $guide = Guide::findOrFail($id);
        $guide->delete();

        return response()->json([
            'message' => 'Guide deleted successfully'
        ], 200);
    }
   
}
