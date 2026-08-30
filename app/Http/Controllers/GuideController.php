<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuideController extends Controller
{
    public function index()
    {
        return response()->json(Guide::all());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name'    => ['required', 'string', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:20'],
            'email'        => ['required', 'email', 'max:255', 'unique:guides,email'],
            'password'     => ['required', 'string', 'min:8'],
        ]);

        $guide = Guide::create([
            'full_name'     => $validated['full_name'],
            'phone'         => $validated['phone'] ?? null,
            'email'         => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
        ]);

        return response()->json($guide, 201);
    }

    public function show(string $id)
    {
        $guide = Guide::find($id);

        if (! $guide) {
            return response()->json(['message' => 'Guide not found'], 404);
        }

        return response()->json($guide);
    }

    public function update(Request $request, string $id)
    {
        $guide = Guide::find($id);

        if (! $guide) {
            return response()->json(['message' => 'Guide not found'], 404);
        }

        $validated = $request->validate([
            'full_name' => ['sometimes', 'string', 'max:255'],
            'phone'     => ['nullable', 'string', 'max:20'],
            'email'     => ['sometimes', 'email', 'max:255', Rule::unique('guides', 'email')->ignore($guide->guide_id, 'guide_id')],
            'password'  => ['sometimes', 'string', 'min:8'],
        ]);

        $guide->fill($validated);

        if (! empty($validated['password'])) {
            $guide->password_hash = Hash::make($validated['password']);
        }

        $guide->save();

        return response()->json($guide);
    }

    public function destroy(string $id)
    {
        $guide = Guide::find($id);

        if (! $guide) {
            return response()->json(['message' => 'Guide not found'], 404);
        }

        $guide->delete();

        return response()->json(['message' => 'Guide deleted successfully'], 200);
    }
}
