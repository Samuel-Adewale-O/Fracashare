<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Http\Requests\SupportTicketRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewSupportTicket;
use App\Notifications\TicketStatusUpdated;

class SupportTicketController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware(['role:admin|support'])->except(['index', 'show', 'store', 'reply']);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $query = $user->hasRole(['admin', 'support']) ? SupportTicket::query() : $user->supportTickets();

        $tickets = $query
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->when($request->priority, fn($q, $priority) => $q->where('priority', $priority))
            ->when($request->category, fn($q, $category) => $q->where('category', $category))
            ->with(['user:id,first_name,last_name,email', 'assignedTo:id,first_name,last_name'])
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => 'success',
            'data' => $tickets
        ]);
    }

    public function store(SupportTicketRequest $request)
    {
        $ticket = DB::transaction(function () use ($request) {
            $ticket = SupportTicket::create([
                'user_id' => $request->user()->id,
                'subject' => $request->subject,
                'category' => $request->category,
                'priority' => $request->priority,
                'description' => $request->description
            ]);

            // Create initial message
            $ticket->messages()->create([
                'user_id' => $request->user()->id,
                'message' => $request->description,
                'is_staff_reply' => false
            ]);

            // Notify support staff
            $supportStaff = User::role('support')->get();
            Notification::send($supportStaff, new NewSupportTicket($ticket));

            return $ticket;
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Support ticket created successfully',
            'data' => $ticket->load(['user:id,first_name,last_name,email'])
        ], 201);
    }

    public function show(SupportTicket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'user:id,first_name,last_name,email',
            'assignedTo:id,first_name,last_name',
            'messages' => fn($query) => $query->with('user:id,first_name,last_name')->latest()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $ticket
        ]);
    }

    public function reply(SupportTicket $ticket, Request $request)
    {
        $this->authorize('reply', $ticket);

        $message = $ticket->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'is_staff_reply' => $request->user()->hasRole(['admin', 'support'])
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Reply added successfully',
            'data' => $message->load('user:id,first_name,last_name')
        ]);
    }

    public function assign(SupportTicket $ticket, Request $request)
    {
        $this->authorize('manage', $ticket);

        $request->validate([
            'assigned_to' => 'required|exists:users,id'
        ]);

        $ticket->update([
            'assigned_to' => $request->assigned_to,
            'status' => 'in_progress'
        ]);

        // Notify the assigned staff member
        $assignedStaff = User::find($request->assigned_to);
        $assignedStaff->notify(new TicketStatusUpdated($ticket));

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket assigned successfully',
            'data' => $ticket->load(['assignedTo:id,first_name,last_name'])
        ]);
    }

    public function updateStatus(SupportTicket $ticket, Request $request)
    {
        $this->authorize('manage', $ticket);

        $request->validate([
            'status' => 'required|in:in_progress,resolved,closed'
        ]);

        $ticket->update([
            'status' => $request->status,
            'resolved_at' => in_array($request->status, ['resolved', 'closed']) ? now() : null
        ]);

        // Notify the ticket owner
        $ticket->user->notify(new TicketStatusUpdated($ticket));

        return response()->json([
            'status' => 'success',
            'message' => 'Ticket status updated successfully',
            'data' => $ticket
        ]);
    }
}