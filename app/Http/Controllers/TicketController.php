<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Ticket::with(['user', 'technician']);

        // Role-based filtering
        if ($user->isTechnician()) {
            $query->where('technician_id', $user->id)
                  ->whereIn('status', ['open', 'in_progress']);
        } elseif ($user->isUser()) {
            $query->where('user_id', $user->id);
        }
        // Admin sees all

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $tickets = $query->latest()->paginate(10)->withQueryString();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:hardware,software,jaringan,lainnya',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['status'] = 'open';

        $ticket = Ticket::create($validated);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil dibuat.');
    }

    public function show(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $ticket->load(['user', 'technician', 'comments.user', 'histories.user']);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $technicians = User::technicians()->get();

        return view('tickets.edit', compact('ticket', 'technicians'));
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $user = Auth::user();
        $oldData = $ticket->only(['title', 'description', 'category', 'priority', 'status', 'technician_id']);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|required|string',
            'category' => 'sometimes|required|in:hardware,software,jaringan,lainnya',
            'priority' => 'sometimes|required|in:low,medium,high,urgent',
            'status' => 'sometimes|required|in:open,in_progress,resolved,closed',
            'technician_id' => 'nullable|exists:users,id',
        ]);

        // Only admin can assign technician and change status
        if (!$user->isAdmin()) {
            unset($validated['technician_id'], $validated['status']);
        }

        // Set timestamps based on status
        if (isset($validated['status'])) {
            if ($validated['status'] === 'resolved' && $ticket->status !== 'resolved') {
                $validated['resolved_at'] = now();
            } elseif ($validated['status'] === 'closed' && $ticket->status !== 'closed') {
                $validated['closed_at'] = now();
            }
        }

        $ticket->update($validated);

        // Log changes
        foreach ($validated as $field => $newValue) {
            if (isset($oldData[$field]) && $oldData[$field] != $newValue) {
                TicketHistory::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => $user->id,
                    'field' => $field,
                    'old_value' => $oldData[$field],
                    'new_value' => $newValue,
                ]);
            }
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Ticket berhasil diperbarui.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorizeTicket($ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')
            ->with('success', 'Ticket berhasil dihapus.');
    }

    public function assign(Request $request, Ticket $ticket)
    {
        if (!Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
        ]);

        $oldTechnicianId = $ticket->technician_id;
        $ticket->update($validated);

        if ($oldTechnicianId != $validated['technician_id']) {
            TicketHistory::create([
                'ticket_id' => $ticket->id,
                'user_id' => Auth::id(),
                'field' => 'technician_id',
                'old_value' => $oldTechnicianId,
                'new_value' => $validated['technician_id'],
            ]);
        }

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Teknisi berhasil ditugaskan.');
    }

    private function authorizeTicket(Ticket $ticket)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isTechnician() && $ticket->technician_id === $user->id) {
            return true;
        }

        if ($user->isUser() && $ticket->user_id === $user->id) {
            return true;
        }

        abort(403, 'Anda tidak memiliki akses ke ticket ini.');
    }
}
