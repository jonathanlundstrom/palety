<?php

namespace App\Events;

use App\Models\Parcel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ParcelSaved {
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Parcel $parcel) {
        // No need for any additional logic.
    }
}
