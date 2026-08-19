<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Team::with(['manager', 'territory', 'members']);

        if ($request->filled('territory_id')) {
            $query->where('territory_id', $request->territory_id);
        }

        $teams = $query->get();
        return response()->json($teams);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'territory_id' => 'nullable|exists:territories,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $team = Team::create($validated);
        return response()->json($team->load('manager', 'territory'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $team = Team::with(['manager', 'territory', 'members', 'opportunities'])->findOrFail($id);
        return response()->json($team);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $team = Team::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'territory_id' => 'nullable|exists:territories,id',
            'manager_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $team->update($validated);
        return response()->json($team->fresh(['manager', 'territory', 'members']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $team = Team::findOrFail($id);
        $team->delete();
        return response()->json(['message' => 'Team deleted successfully.']);
    }
}
