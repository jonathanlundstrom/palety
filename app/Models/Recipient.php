<?php

namespace App\Models;

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipient extends Model {
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'recipients';

    /**
     * The attributes that are mass-assignable.
     *
     * @var list<string>
     */
    public $fillable = [
        'name',
        'type',
        'email',
        'phone_number',
        'delivery_type',
        'address',
        'zipcode',
        'nova_poshta_id',
        'city',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => RecipientType::class,
        'delivery_type' => DeliveryType::class,
    ];

    /**
     * Get the recipient's parent model, if any.
     * This is used to establish a hierarchical relationship, like a recipient being part of a group or organization.
     */
    public function parent(): BelongsTo {
        return $this->belongsTo(Recipient::class, 'parent_id');
    }

    /**
     * Get the pallets associated with the recipient.
     */
    public function pallets(): HasMany {
        return $this->hasMany(Pallet::class);
    }

    /**
     * Get the parcels associated with the recipient.
     * These are typically individual items or packages that are sent to the recipient.
     */
    public function parcels(): HasMany {
        return $this->hasMany(Parcel::class);
    }
}
