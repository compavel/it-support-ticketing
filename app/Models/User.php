<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function reportedTickets()
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }

    public function assignedTickets()
    {
        return $this->hasMany(Ticket::class, 'technician_id');
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
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTechnicians($query)
    {
        return $query->where('role', 'technician')->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    // Helpers
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isTechnician()
    {
        return $this->role === 'technician';
    }

    public function isUser()
    {
        return $this->role === 'user';
    }

    public function roleBadgeCss()
    {
        return [
            'admin' => 'bg-purple-100 text-purple-800',
            'technician' => 'bg-blue-100 text-blue-800',
            'user' => 'bg-green-100 text-green-800',
        ][$this->role] ?? 'bg-gray-100 text-gray-800';
    }
}
