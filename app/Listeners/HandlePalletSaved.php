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
        if ($config = $this->getLabelPrinter()) {
            list($printer_ip, $printer_port) = $config;
            PrintLabel::dispatch($event->pallet, $printer_ip, $printer_port);
        } else {
            logger()->info('No label printer configured. Discarding label print event.');
        }
    }
}
