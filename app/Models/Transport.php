<?php

namespace App\Models;

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
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    public $fillable = [
        'notes',
        'delivered_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'delivered_at' => 'datetime_immutable',
    ];

    /**
     * Get the pallets associated with the transport.
     */
    public function pallets(): HasMany {
        return $this->hasMany(Pallet::class);
    }

    /**
     * Get the parcels associated with the transport.
     */
    public function parcels(): HasMany {
        return $this->hasMany(Parcel::class);
    }
}
