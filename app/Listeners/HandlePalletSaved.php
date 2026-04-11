<?php

namespace App\Listeners;

use App\Events\PalletSaved;
use App\Jobs\PrintLabel;
use App\Jobs\Traits\ListenerHelpers;

class HandlePalletSaved {
    use ListenerHelpers;

    /**
     * Handle the event.
     * @param PalletSaved $event
     * @return void
     */
    public function handle(PalletSaved $event): void {
        if (config('printing.enabled')) {
            PrintLabel::dispatch($event->pallet);
        } else {
            logger()->info('No label printer configured. Discarding label print event.');
        }
    }
}
