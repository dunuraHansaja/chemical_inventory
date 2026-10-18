<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'is_active',
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
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->is_active && $this->role?->grants($permission) === true;
    }

    public function landingRoute(): string
    {
        foreach ([
            'dashboard.view' => 'dashboard',
            'materials.view' => 'materials.index',
            'lots.view' => 'lots.index',
            'production.view' => 'production.index',
            'purchases.view' => 'purchases.index',
            'suppliers.view' => 'suppliers.index',
            'categories.view' => 'categories.index',
            'movements.view' => 'movements.index',
            'adjustments.create' => 'adjustments.create',
            'settings.view' => 'settings.edit',
            'roles.manage' => 'settings.roles.index',
            'users.manage' => 'settings.users.index',
        ] as $permission => $route) {
            if ($this->hasPermission($permission)) {
                return $route;
            }
        }

        return 'dashboard';
    }
}
