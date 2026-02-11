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
     * @param ParcelSaved $event
     * @return void
     */
    public function handle(ParcelSaved $event): void {
        if ($config = $this->getLabelPrinter()) {
            list($printer_ip, $printer_port) = $config;
            PrintLabel::dispatch($event->parcel, $printer_ip, $printer_port);

            if ($event->parcel->pallet_id) {
                $pallet = $event->parcel->pallet;
                if ($pallet->type === PalletType::CALCULATED) {
                    PrintLabel::dispatch($pallet, $printer_ip, $printer_port); // Re-print the associated pallet label.
                }
            }
        } else {
            logger()->info('No label printer configured. Discarding label print event.');
        }
    }
}
