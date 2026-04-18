<?php

namespace App\Http\Controllers;

use App\Models\Recipient;
use App\Models\Transport;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;
use Spatie\Browsershot\Browsershot;

class TransportController extends Controller {

    /**
     * Render the packing list as plain HTML for Browsershot access via signed URL.
     * @param Transport $transport
     * @return View
     */
    public function showPackingList(Transport $transport): View {
        return view('exports.transport-packing-list', [
            'transport' => $transport,
            'loadedByRecipient' => $this->buildLoadedByRecipient($transport),
        ]);
    }

    /**
     * Generate a PDF of the packing list using Browsershot and return it as a download.
     * @param Transport $transport
     * @return Response
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
            'Content-Disposition' => 'attachment; filename="packing-list-' . $transport->id . '.pdf"',
        ]);
    }

    /**
     * Build the loaded-by-recipient data structure for the packing list view.
     * @param Transport $transport
     * @return array
     */
    private function buildLoadedByRecipient(Transport $transport): array {
        $pallets = $transport->pallets()
            ->with(['recipient', 'parcels'])
            ->get()
            ->groupBy('recipient_id');

        $parcels = $transport->parcels()
            ->with('recipient')
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
            $loadedGoods[$recipientId]['pallets'] = $items;
            $loadedGoods[$recipientId]['weight'] += $items->sum(fn ($pallet) => $pallet->getWeight());
        }

        foreach ($parcels as $recipientId => $items) {
            $loadedGoods[$recipientId]['parcels'] = $items;
            $loadedGoods[$recipientId]['weight'] += $items->sum(fn ($parcel) => $parcel->getWeight());
        }

        return $loadedGoods;
    }
}
