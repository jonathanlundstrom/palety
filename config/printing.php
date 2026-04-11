<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Label printing
    |--------------------------------------------------------------------------
    |
    | This value determines if the cloud-application should dispatch the
    | local queue job involved in printing a label for a parcel or pallet.
    |
    */

    'enabled' => env('LABEL_PRINTING_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Printer IP Address
    |--------------------------------------------------------------------------
    |
    | The local ip address of the label printer. Not used by the cloud runtime.
    |
    */

    'printer_ip' => env('LABEL_PRINTER_IP'),

    /*
    |--------------------------------------------------------------------------
    | Printer TCP port
    |--------------------------------------------------------------------------
    |
    | The local TCP port of the label printer. Not used by the cloud runtime.
    | Defaults to port 9100 which is standard for Zebra, Intermec, Honeywell,
    | and many other label printers.
    |
    */

    'printer_port' => env('LABEL_PRINTER_PORT', 9100),

];
