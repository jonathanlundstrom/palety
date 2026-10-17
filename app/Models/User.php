<?php

namespace App\Models;

use App\Enumerables\Permission;
use App\Models\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Shipfastlabs\Grant\HasRoles;

class User extends Authenticatable {
    use HasFactory, HasRoles, ModelHelpers, Notifiable;

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'timezone',
    ];

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
    protected function casts(): array {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Users who may manage super admins can only be deleted by someone who may as well.
     * Without an authenticated user (e.g. queues or the console), such users cannot be deleted.
     */
    protected static function booted(): void {
        static::deleting(fn (User $user) => $user->cannot(Permission::MANAGE_SUPER_ADMINS)
            || auth()->user()?->can(Permission::MANAGE_SUPER_ADMINS));
    }

    /**
     * Get the user's initials
     */
    public function initials(): string {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }
}
