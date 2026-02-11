<?php

namespace App\Events;

use App\Models\Pallet;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PalletSaved {
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Pallet $pallet) {
        // No need for any additional logic.
    }
}
