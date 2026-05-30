<?php

namespace App\Listeners;

use App\Enumerables\PalletType;
use App\Events\ParcelSaved;
use App\Jobs\PrintLabel;
use App\Jobs\Traits\ListenerHelpers;

class HandleParcelSaved {
    use ListenerHelpers;

    /**
     * Handle the event.
     */
    public function handle(ParcelSaved $event): void {
        if (config('printing.enabled')) {
            PrintLabel::dispatch($event->parcel);

            if ($event->parcel->pallet_id) {
                $pallet = $event->parcel->pallet;
                if ($pallet->type === PalletType::CALCULATED) {
                    PrintLabel::dispatch($pallet); // Re-print the associated pallet label.
                }
            }
        } else {
            logger()->info('No label printer configured. Discarding label print event.');
        }
    }
}
