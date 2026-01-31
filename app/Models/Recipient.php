<?php

namespace App\Models;

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use Illuminate\Database\Eloquent\Builder;
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
        'parent_id',
        'name',
        'type',
        'reference',
        'email',
        'phone_number',
        'organisation_number',
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

    /**
     * Scope a query to select distinct cities and order them alphabetically.
     *
     * @param Builder $query
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeCities(Builder $query): Builder {
        return $query->select('city')
            ->distinct()
            ->orderBy('city');
    }

    /**
     * Apply a query scope to list records with specified columns and order.
     *
     * @param Builder $query The query builder instance.
     * @param array $columns The columns to select in the query.
     * @param string $order_by The column to order the results by.
     *
     * @return Builder The modified query builder instance.
     */
    public function scopeList(Builder $query, array $columns, string $order_by): Builder {
        return $query->select(...$columns)
            ->orderBy($order_by);
    }
}
