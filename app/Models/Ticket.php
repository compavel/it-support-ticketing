<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'technician_id',
        'title',
        'description',
        'category',
        'priority',
        'status',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function comments()
    {
        return $this->hasMany(TicketComment::class);
    }

    public function histories()
    {
        return $this->hasMany(TicketHistory::class);
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    public function scopeHigh($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTechnician($query, $userId)
    {
        return $query->where('technician_id', $userId);
    }

    // Helpers
    public function isActive()
    {
        return in_array($this->status, ['open', 'in_progress']);
    }

    public function statusLabel()
    {
        return match ($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst($this->status),
        };
    }

    public function priorityLabel()
    {
        return match ($this->priority) {
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
            'urgent' => 'Urgent',
            default => ucfirst($this->priority),
        };
    }

    public function categoryLabel()
    {
        return match ($this->category) {
            'hardware' => 'Hardware',
            'software' => 'Software',
            'jaringan' => 'Jaringan',
            'lainnya' => 'Lainnya',
            default => ucfirst($this->category),
        };
    }

    public function statusBadgeCss()
    {
        return [
            'open' => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
            'in_progress' => 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20',
            'resolved' => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
            'closed' => 'bg-gray-50 text-gray-600 ring-1 ring-gray-500/20',
        ][$this->status] ?? 'bg-gray-50 text-gray-600';
    }

    public function statusDotCss()
    {
        return [
            'open' => 'bg-blue-500',
            'in_progress' => 'bg-amber-500',
            'resolved' => 'bg-emerald-500',
            'closed' => 'bg-gray-400',
        ][$this->status] ?? 'bg-gray-400';
    }

    public function priorityBadgeCss()
    {
        return [
            'urgent' => 'bg-red-100 text-red-700',
            'high' => 'bg-orange-100 text-orange-700',
            'medium' => 'bg-blue-100 text-blue-700',
            'low' => 'bg-gray-100 text-gray-600',
        ][$this->priority] ?? 'bg-gray-100 text-gray-600';
    }

    public function progressPercent($field)
    {
        $total = static::count();
        if ($total === 0) {
            return 0;
        }

        return round((static::where($field, $this->$field)->count() / $total) * 100);
    }
}
