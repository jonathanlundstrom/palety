<?php namespace App\Jobs\Traits;

trait ListenerHelpers {

    /**
     * Retrieves the IP and port of the label printer from the environment variables.
     * Return null if no configuration is found.
     * @return array|null
     */
    private function getLabelPrinter(): array|null {
        $printer_ip = env('LABEL_PRINTER_IP');
        $printer_port = env('LABEL_PRINTER_PORT');
        if ($printer_ip && $printer_port) {
            return [$printer_ip, $printer_port];
        } else {
            return null;
        }
    }

}
