<?php

namespace App\Http\Controllers;

use App\Models\Recipient;
use App\Models\Transport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Browsershot\Browsershot;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransportController extends Controller {
    /**
     * Render the packing list as plain HTML for Browsershot access via signed URL.
     */
    public function showPackingList(Transport $transport): View {
        return view('exports.packing-list-pdf', [
            'transport' => $transport,
            'loadedByRecipient' => $this->buildLoadedByRecipient($transport),
        ]);
    }

    /**
     * Generate a PDF of the packing list using Browsershot and return it as a download.
     */
    public function printPackingList(Transport $transport): Response {
        $signedUrl = URL::temporarySignedRoute('transports.packing-list.show', now()->addMinutes(5), [
            'transport' => $transport,
        ]);

        $browsershot = Browsershot::url($signedUrl)
            ->noSandbox()
            ->showBackground()
            ->emulateMedia('print')
            ->hideBrowserHeaderAndFooter()
            ->setOption('args', [
                '--headless',
                '--no-sandbox',
                '--disable-dev-shm-usage',
                '--disable-features=VizDisplayCompositor',
                '--disable-gpu',
                '--disable-setuid-sandbox',
                '--disable-software-rasterizer',
            ])
            ->setNodeModulePath(config('browsershot.node_modules_path'))
            ->format('A4');

        if ($chromePath = config('browsershot.chrome_path')) {
            $browsershot->setChromePath($chromePath);
        }

        return response($browsershot->pdf(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="packing-list-'.$transport->id.'.pdf"',
        ]);
    }

    /**
     * Generate an Excel file of the packing list and return it as a download.
     */
    public function downloadPackingListXlsx(Transport $transport): BinaryFileResponse {
        App::setLocale('en'); // Force English locale.

        $loadedByRecipient = $this->buildLoadedByRecipient($transport);
        $export = new class($transport, $loadedByRecipient) implements FromView {
            public function __construct(
                private readonly Transport $transport,
                private readonly array $loadedByRecipient,
            ) {}

            public function view(): View {
                return view('exports.packing-list-excel', [
                    'transport' => $this->transport,
                    'loadedByRecipient' => $this->loadedByRecipient,
                ]);
            }
        };

        return Excel::download($export, 'packing-list-'.$transport->id.'.xlsx');
    }

    /**
     * Build the loaded-by-recipient data structure for the packing list view.
     */
    private function buildLoadedByRecipient(Transport $transport): array {
        $pallets = $transport->pallets()
            ->with(['recipient', 'content', 'parcels.content'])
            ->get()
            ->groupBy('recipient_id');

        $parcels = $transport->parcels()
            ->with(['recipient', 'content'])
            ->get()
            ->groupBy('recipient_id');

        $recipients = Recipient::whereIn('id', array_unique([
            ...$pallets->keys()->toArray(),
            ...$parcels->keys()->toArray(),
        ]))
            ->orderBy('name')
            ->get();

        $loadedGoods = [];

        foreach ($recipients as $recipient) {
            $loadedGoods[$recipient->id] = [
                'model' => $recipient,
                'pallets' => collect(),
                'parcels' => collect(),
                'weight' => 0,
            ];
        }

        foreach ($pallets as $recipientId => $items) {
            $loadedGoods[$recipientId]['pallets'] = $items->sortBy(
                fn ($pallet) => $pallet->displayContent()->first()->label_en ?? ''
            )->values();
            $loadedGoods[$recipientId]['weight'] += $items->sum(fn ($pallet) => $pallet->getWeight());
        }

        foreach ($parcels as $recipientId => $items) {
            $loadedGoods[$recipientId]['parcels'] = $items->sortBy(
                fn ($parcel) => $parcel->content->first()->label_en ?? ''
            )->values();
            $loadedGoods[$recipientId]['weight'] += $items->sum(fn ($parcel) => $parcel->getWeight());
        }

        return $loadedGoods;
    }
}
