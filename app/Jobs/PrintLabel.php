<?php

namespace App\Jobs;

use App\Models\Parcel;
use App\Models\Pallet;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PrintLabel implements ShouldQueue {
    use Queueable;

    /**
     * The resource to print the label for.
     * @var Parcel|Pallet $resource
     */
    private Parcel|Pallet $resource;

    /**
     * The IP address of the printer to print the label on.
     * @var string $printer_ip
     */
    private string $printer_ip;

    /**
     * The port of the printer to print the label on.
     * @var int $printer_port
     */
    private int $printer_port;

    /**
     * Create a new job instance.
     * @param Parcel|Pallet $resource
     * @param string $printer_ip
     * @param int $printer_port
     */
    public function __construct(Parcel|Pallet $resource, string $printer_ip, int $printer_port) {
        $this->resource = $resource;
        $this->printer_ip = $printer_ip;
        $this->printer_port = $printer_port;
    }

    /**
     * Send the provided ZPL to printer by IP and port.
     * Does not generate a reply on success but throws exceptions on failure.
     * Port 9100 is the default for Zebra printers.
     *
     * @param string $zpl
     * @param string $ip
     * @param int $port
     * @throws Exception
     * @return void
     */
    private function print(string $zpl, string $ip, int $port = 9100): void {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception('Failed to create socket: ' . socket_strerror(socket_last_error()));
        }

        if (!socket_connect($socket, $ip, $port)) {
            $error = socket_strerror(socket_last_error($socket));
            socket_close($socket);
            throw new Exception("Could not connect to printer at {$ip}:{$port} - {$error}");

        }

        socket_write($socket, $zpl, strlen($zpl));
        socket_close($socket);
    }

    /**
     * Execute the job.
     */
    public function handle(): void {
        $zpl = view('labels.76_51_compact', [
            'id' => $this->resource->id,
            'type' => $this->resource instanceof Parcel ? 'PARCEL' : 'PALLET',
            'data' => $this->resource::class . ':' . $this->resource->id,
            'weight' => $this->resource->getWeight(),
        ])->render();

        try {
            $this->print($zpl, $this->printer_ip, $this->printer_port);
        } catch (Exception $e) {
            report($e); // Forward to error handler. Silent failure.
        }
    }
}
