<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Panel;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids;

    protected $table = 'users';

    protected $primaryKey = 'id';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [

        'email',
        'name',
        'first_name',
        'last_name',
        'password',
        'image',
        'role'
    ];

    // public function getFilamentName(): string
    // {
    //     return $this->first_name . ' ' . $this->last_name;
    // }

    protected static function booted(): void
    {
        static::creating(function ($user) {
            $user->name = trim("{$user->first_name} {$user->last_name}");
        });

        static::updating(function ($user) {
            $user->name = trim("{$user->first_name} {$user->last_name}");
        });
    }

    public function getRouteKeyName(): string
    {
        return 'id'; // UUID sebagai primary key
    }

    public function getNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'user') {
            return true;
        }

        return match ($panel->getId()) {
            'admin' => $this->role === 'admin',
            'finance' => $this->role === 'finance',
            'owner' => $this->role === 'owner',
            default => false,
        };
    }

    public function invoices() {
        return $this->hasMany(Invoice::class, 'user_id', 'id');
    }
}
