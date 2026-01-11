<?php

namespace App\Models;

use App\Enumerables\ParcelType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Parcel extends Model {
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'parcels';

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    public $fillable = [
        'type',
        'weight',
        'recipient_id',
        'pallet_id',
        'transport_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => ParcelType::class,
    ];

    /**
     * Get the contents associated with the parcel.
     */
    public function contents(): BelongsToMany {
        return $this->belongsToMany(Content::class);
    }

    /**
     * Get the recipient associated with the parcel.
     */
    public function recipient(): BelongsTo {
        return $this->belongsTo(Recipient::class);
    }

    /**
     * Get the pallet associated with the parcel.
     * This relationship is used to track which pallet a parcel is on.
     */
    public function pallet(): BelongsTo {
        return $this->belongsTo(Pallet::class);
    }

    /**
     * Get the transport associated with the parcel.
     * This relationship is used to track which transport a parcel is on, if not on a pallet.
     */
    public function transport(): BelongsTo {
        return $this->belongsTo(Transport::class);
    }
}
