<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Opportunity;
use App\Models\Activity;
use App\Services\OpportunityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OpportunityController extends Controller
{
    protected OpportunityService $oppService;

    public function __construct(OpportunityService $oppService)
    {
        $this->oppService = $oppService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Opportunity::with(['customer', 'contact', 'assignedUser', 'territory', 'team']);

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where('title', 'like', "%{$s}%")
                  ->orWhereHas('customer', function ($q) use ($s) {
                      $q->where('company_name', 'like', "%{$s}%");
                  });
        }

        $opportunities = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($opportunities);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'customer_id' => 'required|exists:customers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'lead_id' => 'nullable|exists:leads,id',
            'stage' => 'nullable|in:prospecting,qualification,proposal,negotiation,closed_won,closed_lost',
            'probability' => 'nullable|integer|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'close_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',
            'territory_id' => 'nullable|exists:territories,id',
            'competitors' => 'nullable|array',
            'decision_criteria' => 'nullable|string',
            'next_step' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|array',
            'products' => 'nullable|array',
            'products.*.product_id' => 'required_with:products|exists:products,id',
            'products.*.quantity' => 'nullable|integer|min:1',
            'products.*.unit_price' => 'nullable|numeric|min:0',
            'products.*.discount' => 'nullable|numeric|min:0',
        ]);

        $products = $validated['products'] ?? [];
        unset($validated['products']);

        $opp = $this->oppService->createOpportunity($validated, $products, $request->user());
        return response()->json($opp, 201);
    }

    public function show(int $id): JsonResponse
    {
        $opportunity = Opportunity::with([
            'customer.contacts',
            'contact',
            'lead',
            'assignedUser',
            'team',
            'territory',
            'opportunityProducts.product',
            'quotes',
            'orders',
            'activities.performer',
        ])->findOrFail($id);

        return response()->json($opportunity);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $opportunity = Opportunity::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'customer_id' => 'sometimes|required|exists:customers,id',
            'contact_id' => 'nullable|exists:contacts,id',
            'stage' => 'nullable|in:prospecting,qualification,proposal,negotiation,closed_won,closed_lost',
            'probability' => 'nullable|integer|min:0|max:100',
            'amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'close_date' => 'nullable|date',
            'assigned_to' => 'nullable|exists:users,id',
            'team_id' => 'nullable|exists:teams,id',
            'territory_id' => 'nullable|exists:territories,id',
            'competitors' => 'nullable|array',
            'decision_criteria' => 'nullable|string',
            'next_step' => 'nullable|string|max:255',
            'custom_fields' => 'nullable|array',
        ]);

        $opportunity->update($validated);
        return response()->json($opportunity->fresh(['customer', 'contact', 'assignedUser']));
    }

    public function updateStage(Request $request, int $id): JsonResponse
    {
        $opportunity = Opportunity::findOrFail($id);
        $validated = $request->validate([
            'stage' => 'required|in:prospecting,qualification,proposal,negotiation,closed_won,closed_lost',
            'lost_reason' => 'nullable|string|required_if:stage,closed_lost',
        ]);

        $opp = $this->oppService->updateStage($opportunity, $validated['stage'], $validated['lost_reason'] ?? null, $request->user());
        return response()->json($opp);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $opportunity = Opportunity::findOrFail($id);
        $opportunity->delete();
        return response()->json(['message' => 'Opportunity deleted successfully.']);
    }

    public function pipeline(Request $request): JsonResponse
    {
        $userId = $request->get('assigned_to');
        $territoryId = $request->get('territory_id');
        $pipeline = $this->oppService->getPipelineSummary($userId, $territoryId);
        return response()->json($pipeline);
    }

    public function activities(int $id): JsonResponse
    {
        $activities = Activity::where('entity_type', 'opportunity')
            ->where('entity_id', $id)
            ->with(['performer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($activities);
    }

    public function addActivity(Request $request, int $id): JsonResponse
    {
        $opp = Opportunity::findOrFail($id);
        $validated = $request->validate([
            'type' => 'required|in:call,email,meeting,task,note',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
            'duration' => 'nullable|integer',
            'outcome' => 'nullable|string|max:100',
        ]);

        $validated['entity_type'] = 'opportunity';
        $validated['entity_id'] = $opp->id;
        $validated['performed_by'] = $request->user() ? $request->user()->id : null;

        $activity = Activity::create($validated);
        return response()->json($activity->load('performer', 'assignedUser'), 201);
    }
}
