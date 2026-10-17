<?php

namespace App\Models;

use App\Enumerables\Permission;
use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transport extends Model {
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transports';

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    public $fillable = [
        'type',
        'status',
        'notes',
        'sent_at',
        'delivered_at',
        'import_agent_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => TransportType::class,
        'status' => TransportStatus::class,
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'import_agent_id' => 'integer',
    ];

    /**
     * Prevent locked transports from being deleted, unless the current user may also unlock them.
     * Without an authenticated user (e.g. queues or the console), locked transports cannot be deleted.
     */
    protected static function booted(): void {
        static::deleting(fn (Transport $transport) => ! $transport->isLocked()
            || auth()->user()?->can(Permission::UNLOCK_TRANSPORT));
    }

    /**
     * Determine whether the transport is locked. A transport is locked once it is
     * no longer in progress, which also locks every pallet and parcel loaded on it.
     */
    public function isLocked(): bool {
        return $this->status !== TransportStatus::IN_PROGRESS;
    }

    /**
     * Determine whether the given user may see the transport. Users without permission
     * to view all transports only see those they are the listed import agent for.
     */
    public function isVisibleTo(User $user): bool {
        return $user->can(Permission::VIEW_ALL_TRANSPORTS) || $this->import_agent_id === $user->id;
    }

    /**
     * Limit the query to the transports the given user may see.
     * Mirrors isVisibleTo(), so both must be kept in sync.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder {
        return $query->when(
            $user->cannot(Permission::VIEW_ALL_TRANSPORTS),
            fn (Builder $query) => $query->where('import_agent_id', $user->id)
        );
    }

    /**
     * Get the import agent responsible for the transport.
     */
    public function importAgent(): BelongsTo {
        return $this->belongsTo(User::class, 'import_agent_id');
    }

    /**
     * Get the parcels associated with the transport.
     */
    public function parcels(): HasMany {
        return $this->hasMany(Parcel::class);
    }

    /**
     * Get the pallets associated with the transport.
     */
    public function pallets(): HasMany {
        return $this->hasMany(Pallet::class);
    }

    /**
     * Calculate and retrieve the total weight loaded on the transport.
     *
     * @return float The calculated weight of the transport.
     */
    public function getWeight(): float {
        $parcels_weight = $this->parcels()->sum('weight');
        $pallets_weight = $this->pallets()->get()->sum(fn ($pallet) => $pallet->getWeight());

        return floatval($parcels_weight + $pallets_weight);
    }

    /**
     * Calculate and retrieve the total estimated value loaded on the transport.
     *
     * @return float The calculated value of the transport.
     */
    public function getValue(): float {
        $parcels_value = $this->parcels()->sum('value');
        $pallets_value = $this->pallets()->get()->sum(fn ($pallet) => $pallet->getValue());

        return floatval($parcels_value + $pallets_value);
    }
}
