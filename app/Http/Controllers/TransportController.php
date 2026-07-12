<?php

namespace App\Http\Controllers;

use App\Enumerables\ImportCategory;
use App\Enumerables\PalletType;
use App\Enumerables\ParcelType;
use App\Models\Parcel;
use App\Models\Recipient;
use App\Models\Transport;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
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

    public function showImportList(Transport $transport): View {
        App::setLocale('uk');
        return view('exports.import-list-excel', [
            'transport' => $transport,
            'data' => $this->buildLoadedByCategory($transport),
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

    /* private function formatParcel(Parcel $parcel): array {
        return [
            'id' => $parcel->id,
            'label_en' => $content->label_en,
            'label_ua' => $content->label_ua,
            'weight' => $parcel->weight,
        ];
    } */

    /**
     * Find the dominant ImportCategory for a set of content items.
     * The category with the most items wins; ties are broken by ImportCategory enum declaration order.
     */
    private function dominantCategory(Collection $contentItems): string {
        $grouped = $contentItems->groupBy(fn ($c) => $c->category->name);
        $dominant = null;
        $maxCount = 0;

        foreach (ImportCategory::cases() as $category) {
            $count = $grouped->get($category->name)?->count() ?? 0;

            if ($count > $maxCount) {
                $maxCount = $count;
                $dominant = $category->name;
            }
        }

        return $dominant;
    }

    private function tabulateParcels(array &$result, Collection $parcels): void {
        foreach ($parcels as $parcel) {
            if ($parcel->content->count() > 1) {
                $dominant = $this->dominantCategory($parcel->content);
                $dominantContent = $parcel->content->filter(fn ($c) => $c->category->name === $dominant);
                $result[$dominant]['parcels'][] = [
                    'id' => $parcel->id,
                    'label_en' => $dominantContent->pluck('label_en')->filter()->implode(', '),
                    'label_ua' => $dominantContent->pluck('label_ua')->filter()->implode(', '),
                    'quantity' => 1,
                    'weight' => $parcel->weight,
                    'unit' => match($parcel->type) {
                        ParcelType::BOX => 'коробка',
                        ParcelType::OTHER => 'штука',
                    },
                    'notes' => $parcel->notes,
                ];
            } else {
                $content = $parcel->content->first();
                $result[$content->category->name]['parcels'][] = [
                    'id' => $parcel->id,
                    'label_en' => $content->label_en,
                    'label_ua' => $content->label_ua,
                    'quantity' => 1,
                    'weight' => $parcel->weight,
                    'unit' => match($parcel->type) {
                        ParcelType::BOX => 'коробка',
                        ParcelType::OTHER => 'штука',
                    },
                    'notes' => $parcel->notes,
                ];
            }
        }
    }

    private function tabulatePallets(array &$result, Collection $pallets): void {
        foreach ($pallets as $pallet) {
            if ($pallet->type === PalletType::CALCULATED) {
                $this->tabulateParcels($result, $pallet->parcels);
            } else {
                if ($pallet->content->count() > 1) {
                    $dominant = $this->dominantCategory($pallet->content);
                    $dominantContent = $pallet->content->filter(fn ($c) => $c->category->name === $dominant);
                    $result[$dominant]['pallets'][] = [
                        'id' => $pallet->id,
                        'label_en' => $dominantContent->pluck('label_en')->filter()->implode(', '),
                        'label_ua' => $dominantContent->pluck('label_ua')->filter()->implode(', '),
                        'quantity' => 1,
                        'weight' => $pallet->getWeight(),
                        'notes' => $pallet->notes,
                    ];
                } else {
                    $content = $pallet->content->first();
                    $result[$content->category->name]['pallets'][] = [
                        'id' => $pallet->id,
                        'label_en' => $content->label_en,
                        'label_ua' => $content->label_ua,
                        'quantity' => 1,
                        'weight' => $pallet->getWeight(),
                        'notes' => $pallet->notes,
                    ];
                }
            }
        }
    }

    /**
     * Build the loaded-by-category data structure for the import declaration view.
     * Multi-category items are assigned entirely to the dominant category (most content items).
     * Ties are broken by ImportCategory enum declaration order.
     * Calculated pallets are handled at parcel level; manual pallets at pallet level.
     * Empty categories are omitted. Order follows ImportCategory enum declaration.
     */
    public function buildLoadedByCategory(Transport $transport): array {
        $result = [];

        foreach (ImportCategory::cases() as $category) {
            $result[$category->name] = [
                'parcels' => [],
                'pallets' => [],
            ];
        }

        $parcels = $transport->parcels()
            ->with('content')
            ->get();

        $pallets = $transport->pallets()
            ->with('parcels')
            ->with('parcels.content')
            ->get();

        $this->tabulateParcels($result, $parcels);
        $this->tabulatePallets($result, $pallets);

        return array_filter($result, fn ($row) => $row['parcels'] || $row['pallets']);
    }
}
