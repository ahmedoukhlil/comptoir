<?php

namespace App\Models;

use Database\Factories\TenantFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[Fillable(['nom', 'plan', 'statut', 'essai_expire_le'])]
class Tenant extends Model
{
    /** @use HasFactory<TenantFactory> */
    use HasFactory;

    /**
     * Durée de l'essai gratuit accordé automatiquement à la création
     * d'un tenant sur le plan Solo.
     */
    public const JOURS_ESSAI_GRATUIT = 14;

    protected function casts(): array
    {
        return [
            'essai_expire_le' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Tenant $tenant) {
            if ($tenant->plan === 'solo' && ! $tenant->essai_expire_le) {
                $tenant->statut = 'essai';
                $tenant->essai_expire_le = Carbon::now()->addDays(self::JOURS_ESSAI_GRATUIT);
            }
        });
    }

    public function points(): HasMany
    {
        return $this->hasMany(Point::class);
    }

    public function agents(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function alimentations(): HasMany
    {
        return $this->hasMany(Alimentation::class);
    }

    public function peutSuperviserPlusieursPoints(): bool
    {
        return in_array($this->plan, ['reseau', 'entreprise'], true);
    }

    public function aStatistiquesAvancees(): bool
    {
        return $this->plan === 'entreprise';
    }

    public function essaiExpire(): bool
    {
        return $this->statut === 'essai'
            && $this->essai_expire_le !== null
            && $this->essai_expire_le->isPast();
    }

    public function enLectureSeule(): bool
    {
        return $this->statut === 'lecture_seule' || $this->essaiExpire();
    }
}
