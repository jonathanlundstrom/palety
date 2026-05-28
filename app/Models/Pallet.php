<?php

namespace App\Models;

use App\Enumerables\Availability;
use App\Enumerables\PalletStatus;
use App\Enumerables\PalletType;
use App\Events\PalletSaved;
use App\Models\Traits\ModelHelpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

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
        'status',
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
        'status' => PalletStatus::class,
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
     * Get the content associated with the pallet.
     */
    public function content(): BelongsToMany {
        return $this->belongsToMany(Content::class);
    }

    /**
     * Get unique content items for display, sourced from the direct relation
     * for manual pallets, or aggregated from parcels for calculated pallets.
     * @return Collection
     */
    public function displayContent(): Collection {
        if ($this->type === PalletType::CALCULATED) {
            return $this->parcels
                ->flatMap(fn($parcel) => $parcel->content)
                ->unique('id')
                ->values();
        }

        return $this->content;
    }

    /**
     * Get a comma-separated list of pallet content for display.
     * @param string|null $locale
     * @return string
     */
    public function contentList(?string $locale = null): string {
        $field = $locale !== null ? 'label_'.$locale : Content::label();
        return implode(', ', $this->displayContent()->pluck($field)->toArray());
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
        return $this->transport_id !== null
            ? Availability::LOADED_ON_TRANSPORT
            : Availability::AVAILABLE;
    }

    /**
     * Get the categories associated with the pallet.
     * If the pallet is calculated, it will return the categories of the parcels in the pallet.
     * Otherwise, it will return the category of the pallet itself.
     * @return array
     */
    public function getCategories(): array {
        if ($this->type === PalletType::CALCULATED) {
            return Content::query()
                ->whereHas('parcels', fn($q) => $q->where('pallet_id', $this->id))
                ->distinct()
                ->pluck('category')
                ->toArray();
        } else {
            return $this->content->pluck('category')->unique()->toArray();
        }
    }

    /**
     * Scope to pallets that are available (not loaded on a transport).
     */
    public function scopeAvailable(Builder $query): Builder {
        return $query->whereNull('transport_id');
    }

    /**
     * Scope to pallets that have been sent on transport.
     */
    public function scopeSent(Builder $query): Builder {
        return $query->whereIn('transport_id', Transport::whereNotNull('sent_at')->select('id'));
    }
}
