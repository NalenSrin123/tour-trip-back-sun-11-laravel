<?php

namespace App\Http\Controllers;

use App\Models\Guide;
use App\Traits\ApiResponse;
use App\Http\Resources\GuideResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class GuideController extends Controller
{
    use ApiResponse;

    // Get all guides
    public function index()
    {
        try {
            $guides = Guide::latest()->get();

            return $this->success(
                GuideResource::collection($guides),
                'Guides retrieved successfully'
            );
        } catch (Throwable $e) {
            return $this->serverError('Failed to retrieve guides', $e);
        }
    }

    // Create a new Guide
    public function store(Request $request)
    {
        try {
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
                'password_hash' => Hash::make($validated['password']),
            ]);

            return $this->success(
                new GuideResource($guide),
                'Guide created successfully',
                201
            );
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->serverError('Failed to create guide', $e);
        }
    }

    // Get a specific Guide
    public function show(string $id)
    {
        try {
            $guide = Guide::findOrFail($id);

            return $this->success(
                new GuideResource($guide),
                'Guide retrieved successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->error('Guide not found', 404);
        } catch (Throwable $e) {
            return $this->serverError('Failed to retrieve guide', $e);
        }
    }

    // Edit a Guide
    public function update(Request $request, string $id)
    {
        try {
            $guide = Guide::findOrFail($id);

            $validated = $request->validate([
                'full_name' => 'sometimes|string|max:255',
                'phone'     => 'nullable|string|max:20',
                'email'     => ['sometimes', 'email', Rule::unique('guides', 'email')->ignore($guide->guide_id, 'guide_id')],
                'password'  => 'nullable|string|min:6',
            ]);

            if (!empty($validated['password'])) {
                $validated['password_hash'] = Hash::make($validated['password']);
            }
            unset($validated['password']);

            $guide->update($validated);

            return $this->success(
                new GuideResource($guide->fresh()),
                'Guide updated successfully'
            );
        } catch (ModelNotFoundException $e) {
            return $this->error('Guide not found', 404);
        } catch (ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        } catch (Throwable $e) {
            return $this->serverError('Failed to update guide', $e);
        }
    }

    // Delete a Guide
    public function destroy(string $id)
    {
        try {
            $guide = Guide::findOrFail($id);
            $guide->delete();

            return $this->success(null, 'Guide deleted successfully');
        } catch (ModelNotFoundException $e) {
            return $this->error('Guide not found', 404);
        } catch (Throwable $e) {
            return $this->serverError('Failed to delete guide', $e);
        }
    }
}