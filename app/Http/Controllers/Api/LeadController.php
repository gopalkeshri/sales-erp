<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Activity;
use App\Services\LeadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    protected LeadService $leadService;

    public function __construct(LeadService $leadService)
    {
        $this->leadService = $leadService;
    }

    public function index(Request $request): JsonResponse
    {
        $query = Lead::with(['customer', 'assignedUser', 'territory']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }
        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                  ->orWhere('company_name', 'like', "%{$s}%")
                  ->orWhere('contact_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%");
            });
        }

        $leads = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($leads);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'source' => 'nullable|in:website,referral,cold_call,trade_show,social_media,other',
            'source_detail' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'company_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:new,contacted,qualified,unqualified,converted',
            'qualification_score' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ]);

        $lead = $this->leadService->createLead($validated, $request->user());
        return response()->json($lead, 201);
    }

    public function show(int $id): JsonResponse
    {
        $lead = Lead::with(['customer', 'assignedUser', 'territory', 'activities.performer', 'convertedOpportunity'])->findOrFail($id);
        return response()->json($lead);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'source' => 'nullable|in:website,referral,cold_call,trade_show,social_media,other',
            'source_detail' => 'nullable|string|max:255',
            'customer_id' => 'nullable|exists:customers,id',
            'company_name' => 'nullable|string|max:255',
            'contact_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:20',
            'status' => 'nullable|in:new,contacted,qualified,unqualified,converted',
            'qualification_score' => 'nullable|integer|min:0|max:100',
            'assigned_to' => 'nullable|exists:users,id',
            'territory_id' => 'nullable|exists:territories,id',
            'estimated_value' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'expected_close_date' => 'nullable|date',
            'notes' => 'nullable|string',
            'custom_fields' => 'nullable|array',
        ]);

        $lead = $this->leadService->updateLead($lead, $validated, $request->user());
        return response()->json($lead);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $lead->delete();
        return response()->json(['message' => 'Lead deleted successfully.']);
    }

    public function convert(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'opportunity_title' => 'nullable|string|max:255',
            'amount' => 'nullable|numeric|min:0',
        ]);

        $result = $this->leadService->convertLead($lead, $validated, $request->user());
        return response()->json([
            'message' => 'Lead successfully converted to opportunity.',
            'data' => $result,
        ]);
    }

    public function activities(int $id): JsonResponse
    {
        $activities = Activity::where('entity_type', 'lead')
            ->where('entity_id', $id)
            ->with(['performer', 'assignedUser'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($activities);
    }

    public function addActivity(Request $request, int $id): JsonResponse
    {
        $lead = Lead::findOrFail($id);
        $validated = $request->validate([
            'type' => 'required|in:call,email,meeting,task,note',
            'subject' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
            'duration' => 'nullable|integer',
            'outcome' => 'nullable|string|max:100',
        ]);

        $validated['entity_type'] = 'lead';
        $validated['entity_id'] = $lead->id;
        $validated['performed_by'] = $request->user() ? $request->user()->id : null;

        $activity = Activity::create($validated);
        return response()->json($activity->load('performer', 'assignedUser'), 201);
    }
}
