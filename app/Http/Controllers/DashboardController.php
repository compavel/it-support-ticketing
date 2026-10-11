<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function admin()
    {
        $stats = [
            'total' => Ticket::count(),
            'open' => Ticket::open()->count(),
            'in_progress' => Ticket::inProgress()->count(),
            'resolved' => Ticket::resolved()->count(),
            'closed' => Ticket::closed()->count(),
            'urgent' => Ticket::urgent()->where('status', '!=', 'closed')->count(),
            'high' => Ticket::high()->where('status', '!=', 'closed')->count(),
        ];

        $recentTickets = Ticket::with(['user', 'technician'])
            ->latest()
            ->take(10)
            ->get();

        $technicianStats = User::technicians()->withCount(['assignedTickets as active_tickets' => function ($query) {
            $query->whereIn('status', ['open', 'in_progress']);
        }])->get();

        return view('dashboard.admin', compact('stats', 'recentTickets', 'technicianStats'));
    }

    public function technician()
    {
        $user = Auth::user();

        $stats = [
            'assigned' => $user->assignedTickets()->whereIn('status', ['open', 'in_progress'])->count(),
            'open' => $user->assignedTickets()->open()->count(),
            'in_progress' => $user->assignedTickets()->inProgress()->count(),
            'resolved' => $user->assignedTickets()->resolved()->count(),
        ];

        $priorityOrder = ['urgent' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];

        $myTickets = $user->assignedTickets()
            ->with('user')
            ->whereIn('status', ['open', 'in_progress'])
            ->get()
            ->sortBy([
                fn ($a, $b) => $priorityOrder[$a->priority] <=> $priorityOrder[$b->priority],
                fn ($a, $b) => $b->created_at <=> $a->created_at,
            ])
            ->values();

        $recentResolved = $user->assignedTickets()
            ->with('user')
            ->resolved()
            ->latest('resolved_at')
            ->take(5)
            ->get();

        return view('dashboard.technician', compact('stats', 'myTickets', 'recentResolved'));
    }
}
