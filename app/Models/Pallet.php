<?php

namespace App\Models;

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
        'recipient_id',
        'type',
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
    ];

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
     * Get the weight of the pallet based on type and content.
     */
    public function getWeight(): float {
        $weight = $this->weight;
        if ($this->type === PalletType::CALCULATED) {
            $weight = $this->parcels->sum('weight');
        }

        return floatval($weight);
    }
}
