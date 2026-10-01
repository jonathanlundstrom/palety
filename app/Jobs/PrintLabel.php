<?php

namespace App\Jobs;

use App\Models\Pallet;
use App\Models\Parcel;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class PrintLabel implements ShouldQueue {
    use Queueable;

    /**
     * The maximum number of times the job may be attempted.
     * Failed jobs are most likely caused by errors in printer configuration
     * or network issues, at which point re-attempting may not resolve the issue.
     */
    public int $tries = 1;

    /**
     * Target width and height of the label QR-code, in dots.
     */
    private const int QR_SIZE = 300;

    /**
     * Byte-mode data capacity of QR-code versions 1–10 at error correction level Q.
     * Version N is (17 + 4N) modules wide.
     */
    private const array QR_CAPACITIES = [11, 20, 32, 46, 60, 74, 86, 108, 130, 151];

    /**
     * The resource to print the label for.
     */
    private Parcel|Pallet $resource;

    /**
     * Create a new job instance.
     */
    public function __construct(Parcel|Pallet $resource) {
        $this->resource = $resource;
        $this->onQueue('local'); // Should be handled by onsite queue worker.
    }

    /**
     * Send the provided ZPL to printer by IP and port.
     * Does not generate a reply on success but throws exceptions on failure.
     * Port 9100 is the default for Zebra printers.
     *
     * @throws Exception
     */
    private function print(string $zpl, string $ip, int $port): void {
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket === false) {
            throw new Exception('Failed to create socket: '.socket_strerror(socket_last_error()));
        }

        if (! socket_connect($socket, $ip, $port)) {
            $error = socket_strerror(socket_last_error($socket));
            socket_close($socket);
            throw new Exception("Could not connect to printer at {$ip}:{$port} - {$error}");
        }

        socket_write($socket, $zpl, strlen($zpl));
        socket_close($socket);
    }

    /**
     * Calculate the QR-code magnification (1–10) that gets closest to QR_SIZE without exceeding it,
     * keeping the printed size consistent regardless of the data length.
     *
     * @throws Exception
     */
    private function qrMagnification(string $data): int {
        foreach (self::QR_CAPACITIES as $index => $capacity) {
            if (strlen($data) <= $capacity) {
                $modules = 21 + 4 * $index;

                return max(1, min(10, intdiv(self::QR_SIZE, $modules)));
            }
        }

        throw new Exception('QR-code data is too long.');
    }

    /**
     * Execute the job.
     *
     * @throws Exception
     */
    public function handle(): void {
        $printer_ip = config('printing.printer_ip');
        $printer_port = config('printing.printer_port');

        if (empty($printer_ip) || empty($printer_port)) {
            throw new Exception('Printer IP or port is not configured.');
        }

        $data = route('details', [
            'type' => $this->resource->getMorphClass(),
            'id' => $this->resource->id,
        ]);

        $zpl = view('labels.76_51_compact', [
            'id' => $this->resource->id,
            'type' => strtoupper(class_basename($this->resource)),
            'data' => $data,
            'magnification' => $this->qrMagnification($data),
            'weight' => $this->resource->getWeight(),
        ])->render();

        // Attempt to print label on provided printer:
        $this->print($zpl, $printer_ip, (int) $printer_port);
    }
}
