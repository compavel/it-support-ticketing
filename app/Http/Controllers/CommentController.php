<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $validated = $request->validate([
            'comment' => 'required|string',
        ]);

        TicketComment::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'comment' => $validated['comment'],
        ]);

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Komentar berhasil ditambahkan.');
    }

    public function destroy(TicketComment $comment)
    {
        if ($comment->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403);
        }

        $ticket = $comment->ticket;
        $comment->delete();

        return redirect()->route('tickets.show', $ticket)
            ->with('success', 'Komentar berhasil dihapus.');
    }
}
