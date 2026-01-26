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
        'notes',
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
     * Get the content associated with the parcel.
     */
    public function content(): BelongsToMany {
        return $this->belongsToMany(Content::class);
    }

    /**
     * Get a comma-separated list of parcel content for display.
     * @return string
     */
    public function contentList(): string {
        if ($content = $this->content()) {
            return implode(', ', $content->pluck(Content::label())->toArray());
        }

        return '';
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
     * Check if the parcel is already loaded on a pallet.
     * Ideally, it should not be able to be added to multiple pallets or transports.
     * @return bool
     */
    public function isLoadedOnPallet(): bool {
        return $this->pallet_id !== null;
    }

    /**
     * Get the transport associated with the parcel.
     * This relationship is used to track which transport a parcel is on, if not on a pallet.
     */
    public function transport(): BelongsTo {
        return $this->belongsTo(Transport::class);
    }

    /**
     * Check if the parcel is already loaded on transport.
     * Ideally, it should not be able to be added to multiple pallets or transports.
     * @return bool
     */
    public function isLoadedOnTransport(): bool {
        return $this->transport_id !== null;
    }

    /**
     * Check if the parcel is loaded, either on a pallet or transport.
     * Combines checks for both pallet and transport loading states.
     * @return bool
     */
    public function isLoaded(): bool {
        return $this->isLoadedOnPallet() || $this->isLoadedOnTransport();
    }
}
