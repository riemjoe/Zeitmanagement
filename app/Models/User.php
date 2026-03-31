<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    /** Ist der Nutzer Admin? */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /** Lesbare Rollenbezeichnung */
    public function roleName(): string
    {
        return match ($this->role) {
            'admin'  => 'Admin',
            'member' => 'Mitglied',
            default  => ucfirst($this->role),
        };
    }

    public function timers(): HasMany
    {
        return $this->hasMany(Timer::class);
    }
}
