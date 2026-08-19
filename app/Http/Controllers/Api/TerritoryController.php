<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Territory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TerritoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Territory::with(['manager', 'parent', 'children']);

        if ($request->filled('region')) {
            $query->where('region', $request->region);
        }
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $territories = $query->get();
        return response()->json($territories);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_territory_id' => 'nullable|exists:territories,id',
            'manager_id' => 'nullable|exists:users,id',
            'region' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postal_codes' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $territory = Territory::create($validated);
        return response()->json($territory->load('manager', 'parent'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $territory = Territory::with(['manager', 'parent', 'children', 'users', 'teams', 'customers'])->findOrFail($id);
        return response()->json($territory);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $territory = Territory::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_territory_id' => 'nullable|exists:territories,id',
            'manager_id' => 'nullable|exists:users,id',
            'region' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postal_codes' => 'nullable|array',
            'is_active' => 'nullable|boolean',
        ]);

        $territory->update($validated);
        return response()->json($territory->fresh(['manager', 'parent']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $territory = Territory::findOrFail($id);
        $territory->delete();
        return response()->json(['message' => 'Territory deleted successfully.']);
    }

    public function users(int $id): JsonResponse
    {
        $users = User::where('territory_id', $id)->get();
        return response()->json($users);
    }
}
