<?php

namespace App\Models;

use App\Enumerables\Availability;
use App\Enumerables\ParcelType;
use Illuminate\Database\Eloquent\Builder;
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
        'user_id',
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
     * Get the user who created this parcel.
     */
    public function author(): BelongsTo {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the content associated with the parcel.
     */
    public function content(): BelongsToMany {
        return $this->belongsToMany(Content::class);
    }

    /**
     * Get a comma-separated list of parcel content for display.
     */
    public function contentList(?string $locale = null): string {
        if ($content = $this->content()) {
            $field = $locale !== null ? 'label_'.$locale : Content::label();

            return implode(', ', $content->pluck($field)->toArray());
        } else {
            return '';
        }
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

    /**
     * Check if the parcel is loaded, either on a pallet or transport.
     * Combines checks for both pallet and transport loading states.
     */
    public function getAvailability(): Availability {
        return match (true) {
            $this->pallet_id !== null => Availability::LOADED_ON_PALLET,
            $this->transport_id !== null => Availability::LOADED_ON_TRANSPORT,
            default => Availability::AVAILABLE,
        };
    }

    /**
     * Get the weight of the pallet based on type and content.
     */
    public function getWeight(): float {
        return number_format($this->weight, 2);
    }

    /**
     * Scope to parcels that are available (not loaded on a pallet or transport).
     */
    public function scopeAvailable(Builder $query): Builder {
        return $query->whereNull('pallet_id')->whereNull('transport_id');
    }

    /**
     * Scope to parcels that have been sent, either directly
     * via transport or via a pallet on transport.
     */
    public function scopeSent(Builder $query): Builder {
        return $query->where(function ($q) {
            $q->whereHas('transport', fn ($t) => $t->whereNotNull('sent_at'))
                ->orWhereHas('pallet', fn ($p) => $p->whereHas('transport', fn ($t) => $t->whereNotNull('sent_at')));
        });
    }
}
