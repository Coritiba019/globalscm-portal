<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'approval_status',
        'selected_digital_account_id',
        'is_admin',
        'can_access_all', // <— novo (opcional para admins/ops)
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_admin'          => 'boolean',
        'can_access_all'    => 'boolean', // <— novo
    ];

    /**
     * Caso você ainda use GlobalAccount em outro fluxo, mantém a relação.
     * (Não conflita com o novo controle por pivot user_digital_accounts.)
     */
    public function globalAccounts(): HasMany
    {
        return $this->hasMany(GlobalAccount::class);
    }

    /**
     * Relação pivot: contas digitais permitidas ao usuário.
     * Tabela: user_digital_accounts (id, user_id, digital_account_id, timestamps)
     */
    public function digitalAccounts(): HasMany
    {
        return $this->hasMany(UserDigitalAccount::class);
    }

    /** Helper de aprovação */
    public function isApproved(): bool
    {
        return strtoupper((string) $this->approval_status) === 'APPROVED';
    }

    /**
     * Retorna a lista (array<int>) de digital_account_id permitidos.
     * Se can_access_all = true => retorna null (sinaliza "sem filtro").
     */
    public function allowedDigitalIds(): ?array
    {
        if ($this->can_access_all) {
            return null;
        }

        return $this->digitalAccounts()
            ->pluck('digital_account_id')
            ->map(fn ($v) => (int) $v)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Verifica se o usuário pode acessar um digital específico.
     * Se can_access_all = true => sempre true.
     */
    public function canAccessDigital(?int $digitalId): bool
    {
        if (!$digitalId) return false;
        if ($this->can_access_all) return true;

        return $this->digitalAccounts()
            ->where('digital_account_id', $digitalId)
            ->exists();
    }
}
