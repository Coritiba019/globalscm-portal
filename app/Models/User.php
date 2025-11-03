<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Ajuste conforme seu schema de usuários (se usar Breeze, já existe)
    protected $fillable = [
        'name',
        'email',
        'password',
        'approval_status',
        'selected_digital_account_id',
        'is_admin',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin' => 'boolean',
    ];

    // Relação com as contas globais salvas
    public function globalAccounts()
    {
        return $this->hasMany(GlobalAccount::class);
    }

    // Helper para aprovação
    public function isApproved(): bool
    {
        return $this->approval_status === 'APPROVED';
    }
}
