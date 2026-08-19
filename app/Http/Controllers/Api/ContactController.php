<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Contact::with(['customer', 'manager']);

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        if ($request->filled('is_decision_maker')) {
            $query->where('is_decision_maker', $request->boolean('is_decision_maker'));
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                  ->orWhere('last_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('designation', 'like', "%{$s}%");
            });
        }

        $contacts = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));
        return response()->json($contacts);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'is_decision_maker' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'reports_to' => 'nullable|exists:contacts,id',
            'linkedin_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['created_by'] = $request->user() ? $request->user()->id : null;
        $contact = Contact::create($validated);

        return response()->json($contact->load('customer'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $contact = Contact::with(['customer', 'manager', 'directReports', 'opportunities'])->findOrFail($id);
        return response()->json($contact);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $validated = $request->validate([
            'customer_id' => 'sometimes|required|exists:customers,id',
            'first_name' => 'sometimes|required|string|max:100',
            'last_name' => 'sometimes|required|string|max:100',
            'email' => 'nullable|email|max:191',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'designation' => 'nullable|string|max:100',
            'department' => 'nullable|string|max:100',
            'is_decision_maker' => 'nullable|boolean',
            'is_primary' => 'nullable|boolean',
            'reports_to' => 'nullable|exists:contacts,id',
            'linkedin_url' => 'nullable|string|max:255',
            'twitter_url' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $contact->update($validated);
        return response()->json($contact->fresh(['customer', 'manager']));
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $contact = Contact::findOrFail($id);
        $contact->delete();
        return response()->json(['message' => 'Contact deleted successfully.']);
    }
}
