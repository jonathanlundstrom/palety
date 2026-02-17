<?php

namespace App\Models;

use App\Enumerables\Availability;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletType;
use App\Models\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pallet extends Model {
    use ModelHelpers, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pallets';

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    public $fillable = [
        'user_id',
        'recipient_id',
        'type',
        'category',
        'label_en',
        'label_ua',
        'weight',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => PalletType::class,
        'category' => ImportCategory::class,
    ];

    /**
     * Get the user who created this parcel.
     */
    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the recipient associated with the pallet.
     */
    public function recipient(): BelongsTo {
        return $this->belongsTo(Recipient::class);
    }

    /**
     * Get the parcels associated with the pallet.
     */
    public function parcels(): HasMany {
        return $this->hasMany(Parcel::class);
    }

    /**
     * Get the transport associated with the pallet.
     * This relationship is used to track which transport a pallet is loaded on.
     */
    public function transport(): BelongsTo {
        return $this->belongsTo(Transport::class);
    }

    /**
     * Check if the pallet is already loaded on transport.
     * Ideally, it should not be able to be added to multiple transports.
     * @return Availability
     */
    public function getTransportStatus(): Availability {
        return $this->transport_id !== null
            ? Availability::LOADED_ON_TRANSPORT
            : Availability::AVAILABLE;
    }

    /**
     * Get the weight of the pallet based on type and content.
     */
    public function getWeight(): float {
        $weight = $this->weight;
        if ($this->type === PalletType::CALCULATED) {
            $weight = $this->parcels()->sum('weight');
        }

        return number_format($weight, 2);
    }

    /**
     * Check if the pallet is loaded on transport or available.
     * @return Availability
     */
    public function getAvailability(): Availability {
        return $this->getTransportStatus() === Availability::LOADED_ON_TRANSPORT
            ? Availability::ALREADY_LOADED
            : Availability::AVAILABLE;
    }

    public function getCategories(): array {
        if ($this->type === PalletType::CALCULATED) {
            return Content::query()
                ->whereHas('parcels', fn($q) => $q->where('pallet_id', $this->id))
                ->distinct()
                ->pluck('category')
                ->toArray();
        } else {
            return [$this->category];
        }
    }
}
