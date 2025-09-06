<?php

declare(strict_types=1);

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SupportController extends Controller
{
    /**
     * Get support tickets (Internal: all, Tenant: own tickets only).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $query = SupportTicket::query();

        // Internal users can see all tickets, tenants only their own
        if ($user->isInternal()) {
            // Internal filters
            if ($request->filled('tenant_id')) {
                $query->where('tenant_id', $request->tenant_id);
            }

            if ($request->filled('agent_id')) {
                $query->where('agent_id', $request->agent_id);
            }

            if ($request->filled('unassigned')) {
                $query->whereNull('agent_id');
            }
        } else {
            // Tenant users only see their tenant's tickets
            $query->where('tenant_id', $user->tenant_id);
        }

        // Common filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('subject', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('description', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Special filters
        if ($request->filled('overdue')) {
            $query->overdue();
        }

        if ($request->filled('open_only')) {
            $query->open();
        }

        $tickets = $query->with(['tenant', 'user', 'agent'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'tickets' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
                'last_page' => $tickets->lastPage()
            ]
        ]);
    }

    /**
     * Create a new support ticket (Tenant only).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'category' => ['required', Rule::in(['technical', 'billing', 'feature_request', 'bug_report', 'other'])],
            'metadata' => 'nullable|array',
        ]);

        $user = $request->user();
        if (!$user->tenant_id) {
            return response()->json(['error' => 'Tenant information required'], 403);
        }

        $ticket = SupportTicket::create([
            ...$validated,
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
        ]);

        // Add initial message
        $ticket->addMessage([
            'type' => 'user',
            'author_id' => $user->id,
            'author_name' => $user->first_name . ' ' . $user->last_name,
            'content' => $validated['description'],
        ]);

        return response()->json($ticket->load(['tenant', 'user']), 201);
    }

    /**
     * Get a specific ticket.
     */
    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        $user = $request->user();

        // Check access rights
        if (!$user->isInternal() && $ticket->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        return response()->json($ticket->load(['tenant', 'user', 'agent']));
    }

    /**
     * Add a message to a ticket.
     */
    public function addMessage(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'content' => 'required|string',
            'type' => ['nullable', Rule::in(['user', 'agent', 'internal'])],
        ]);

        $user = $request->user();

        // Check access rights
        if (!$user->isInternal() && $ticket->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Determine message type
        $messageType = $user->isInternal() ? 'agent' : 'user';
        if (isset($validated['type'])) {
            $messageType = $validated['type'];
        }

        $message = [
            'type' => $messageType,
            'author_id' => $user->id,
            'author_name' => $user->first_name . ' ' . $user->last_name,
            'content' => $validated['content'],
        ];

        $ticket->addMessage($message);

        // Update ticket status if needed
        if ($messageType === 'agent' && $ticket->status === 'waiting_user') {
            $ticket->update(['status' => 'in_progress']);
        } elseif ($messageType === 'user' && in_array($ticket->status, ['in_progress', 'waiting_user'])) {
            $ticket->update(['status' => 'open']);
        }

        return response()->json($ticket->fresh());
    }

    /**
     * Assign ticket to agent (Internal only).
     */
    public function assign(Request $request, SupportTicket $ticket): JsonResponse
    {
        if (!$request->user('internal') || !($request->user('internal') instanceof \App\Models\InternalAdmin)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'agent_id' => 'required|exists:users,id',
        ]);

        // Verify agent is internal
        $agent = User::find($validated['agent_id']);
        if (!$agent->isInternal()) {
            return response()->json(['error' => 'Agent must be internal user'], 400);
        }

        $ticket->assignTo($validated['agent_id']);

        // Add internal message
        $ticket->addMessage([
            'type' => 'internal',
            'author_id' => $request->user('internal')->id,
            'author_name' => $request->user('internal')->first_name . ' ' . $request->user('internal')->last_name,
            'content' => "Ticket assigned to {$agent->first_name} {$agent->last_name}",
        ]);

        return response()->json($ticket->fresh()->load('agent'));
    }

    /**
     * Update ticket status (Internal and Tenant).
     */
    public function updateStatus(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['open', 'in_progress', 'waiting_user', 'resolved', 'closed'])],
            'internal_notes' => 'nullable|string',
        ]);

        $user = $request->user();

        // Check access rights
        if (!$user->isInternal() && $ticket->tenant_id !== $user->tenant_id) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Some statuses are internal only
        if (!$user->isInternal() && in_array($validated['status'], ['in_progress', 'waiting_user'])) {
            return response()->json(['error' => 'Status change not allowed'], 403);
        }

        $oldStatus = $ticket->status;
        $newStatus = $validated['status'];

        // Update ticket
        $updateData = ['status' => $newStatus];
        if (isset($validated['internal_notes'])) {
            $updateData['internal_notes'] = $validated['internal_notes'];
        }

        if ($newStatus === 'resolved' && !$ticket->resolved_at) {
            $updateData['resolved_at'] = now();
        } elseif ($newStatus === 'closed' && !$ticket->closed_at) {
            $updateData['closed_at'] = now();
        } elseif (in_array($newStatus, ['open', 'in_progress']) && $ticket->resolved_at) {
            $updateData['resolved_at'] = null;
            $updateData['closed_at'] = null;
        }

        $ticket->update($updateData);

        // Add status change message
        $ticket->addMessage([
            'type' => $user->isInternal() ? 'agent' : 'user',
            'author_id' => $user->id,
            'author_name' => $user->first_name . ' ' . $user->last_name,
            'content' => "Status changed from {$oldStatus} to {$newStatus}",
        ]);

        return response()->json($ticket->fresh());
    }

    /**
     * Get support statistics (Internal only).
     */
    public function statistics(Request $request): JsonResponse
    {
        if (!$request->user('internal') || !($request->user('internal') instanceof \App\Models\InternalAdmin)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $stats = [
            'total_tickets' => SupportTicket::count(),
            'open_tickets' => SupportTicket::open()->count(),
            'unassigned_tickets' => SupportTicket::unassigned()->count(),
            'overdue_tickets' => SupportTicket::overdue()->count(),
            
            'by_status' => SupportTicket::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status'),
                
            'by_priority' => SupportTicket::selectRaw('priority, COUNT(*) as count')
                ->groupBy('priority')
                ->get()
                ->pluck('count', 'priority'),
                
            'by_category' => SupportTicket::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->get()
                ->pluck('count', 'category'),
                
            'average_response_time' => SupportTicket::whereNotNull('first_response_at')
                ->avg('response_time'),
                
            'average_resolution_time' => SupportTicket::whereNotNull('resolved_at')
                ->avg('resolution_time'),
                
            'agents_workload' => SupportTicket::whereNotNull('agent_id')
                ->selectRaw('agent_id, COUNT(*) as ticket_count')
                ->with('agent:id,first_name,last_name')
                ->groupBy('agent_id')
                ->get()
                ->map(function ($item) {
                    return [
                        'agent' => $item->agent,
                        'ticket_count' => $item->ticket_count,
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Get ticket metrics for a specific tenant (Internal only).
     */
    public function tenantMetrics(Request $request, int $tenantId): JsonResponse
    {
        if (!$request->user('internal') || !($request->user('internal') instanceof \App\Models\InternalAdmin)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $metrics = [
            'total_tickets' => SupportTicket::where('tenant_id', $tenantId)->count(),
            'open_tickets' => SupportTicket::where('tenant_id', $tenantId)->open()->count(),
            'resolved_tickets' => SupportTicket::where('tenant_id', $tenantId)->status('resolved')->count(),
            'average_resolution_time' => SupportTicket::where('tenant_id', $tenantId)
                ->whereNotNull('resolved_at')
                ->avg('resolution_time'),
            'satisfaction_score' => null, // TODO: Implement satisfaction tracking
        ];

        return response()->json($metrics);
    }
}