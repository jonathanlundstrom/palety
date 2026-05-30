<?php

namespace App\Models;

use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

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
}
