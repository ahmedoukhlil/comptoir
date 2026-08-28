<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'point_id', 'name', 'telephone', 'password', 'role', 'guide_vu_le'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'guide_vu_le' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function point(): BelongsTo
    {
        return $this->belongsTo(Point::class);
    }

    public function operations(): HasMany
    {
        return $this->hasMany(Operation::class, 'agent_id');
    }

    public function clotures(): HasMany
    {
        return $this->hasMany(Cloture::class, 'agent_id');
    }

    public function estProprietaire(): bool
    {
        return $this->role === 'proprietaire';
    }

    public function estAgent(): bool
    {
        return $this->role === 'agent';
    }

    public function estSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Un propriétaire de tenant Solo n'a pas accès au tableau de bord
     * multi-points (réservé aux plans Réseau/Entreprise) : il utilise
     * l'écran de saisie comme un agent pour son unique point.
     */
    public function peutUtiliserLaCaisse(): bool
    {
        if ($this->estAgent()) {
            return true;
        }

        return $this->estProprietaire()
            && $this->point_id !== null
            && ! $this->tenant?->peutSuperviserPlusieursPoints();
    }
}
