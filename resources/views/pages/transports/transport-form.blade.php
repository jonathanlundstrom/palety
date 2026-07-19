<?php

use App\Enumerables\FormStatus;
use App\Enumerables\Availability;
use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use App\Livewire\Components\FormComponent;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Transport;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[On('edit-resource')]
    public function edit(int $id, string $class): void {
        parent::edit($id, $class);
        $this->linked_pallets = $this->resource->pallets->all();
        $this->linked_parcels = $this->resource->parcels->all();
    }

    #[On('scan-result')]
    public function onScanResult(?array $payload): void {
        if (!is_null($payload) && in_array($payload['class'], [Parcel::class, Pallet::class])) {
            [$target, $toast_key] = match ($payload['class']) {
                Parcel::class => ['linked_parcels', 'toasts.parcel'],
                Pallet::class => ['linked_pallets', 'toasts.pallet'],
            };

            try {
                $abstract = app()->make($payload['class']);
                $object = $abstract::query()
                    ->when($payload['class'] === Pallet::class, fn($q) => $q->with('parcels'))
                    ->findOrFail($payload['id']);

                if ($object->getAvailability() === Availability::AVAILABLE) {
                    if (!in_array($object->id, array_column($this->{$target}, 'id'), true)) {
                        if ($object->recipient) {
                            $this->{$target}[] = $object;
                            Flux::toast(variant: 'success', text: __($toast_key . '.added'));
                            $this->dispatch('vibrate-success');
                        } else {
                            Flux::toast(variant: 'warning', text: __($toast_key . '.no_recipient'));
                        }
                    } else {
                        Flux::toast(variant: 'warning', text: __($toast_key . '.already_added'));
                    }
                } else {
                    Flux::toast(variant: 'danger', text: __($toast_key . '.loaded'));
                }
            } catch (QueryException) {
                Flux::toast(variant: 'danger', text: __($toast_key . '.not_found'));
            }
        }
    }

    #[Validate('required')]
    public string $type = TransportType::TRUCK->name;

    #[Validate('required')]
    public string $status = TransportStatus::IN_PROGRESS->name;

    #[Validate('nullable|array')]
    public array $linked_pallets = [];

    #[Validate('nullable|array')]
    public array $linked_parcels = [];

    #[Validate('nullable')]
    public string $notes;

    /**
     * Remove provided resource from the list of scanned items.
     * @param int $id
     * @param string $class
     * @return void
     */
    #[On('undo-scan')]
    public function undoScan(int $id, string $class): void {
        $field = match ($class) {
            Pallet::class => 'linked_pallets',
            Parcel::class => 'linked_parcels',
        };

        $this->{$field} = array_filter(
            $this->{$field},
            fn($item) => $item->id !== $id
        );
    }

    /**
     * Update transport timestamps based on status.
     * This could potentially override sent_at and/or delivered_at if
     * you step backwards in terms of progress. But that's an odd move.
     * @param array $validated
     * @return void
     */
    protected function updateTimestamps(array &$validated): void {
        $validated = match($this->status) {
            TransportStatus::IN_PROGRESS->name => [
                ...$validated,
                'sent_at' => null,
                'delivered_at' => null,
            ],
            TransportStatus::SENT->name => [
                ...$validated,
                'sent_at' => Carbon::now(),
                'delivered_at' => null,
            ],
            TransportStatus::DELIVERED->name => [
                ...$validated,
                'sent_at' => $this->resource?->sent_at ?? Carbon::now(),
                'delivered_at' => Carbon::now(),
            ],
            default => $validated,
        };
    }

    /**
     * Create new transport from validated form data.
     * @param array $validated
     * @return Transport
     */
    protected function createTransport(array $validated): Transport {
        $transport = Transport::create($validated);
        if (!$transport) throw new Exception('toasts.transport.failed');
        return $transport;
    }

    /**
     * Update existing transport with validated form data.
     * @param array $validated
     * @return Transport
     */
    protected function updateTransport(array $validated): Transport {
        $result = $this->resource->update($validated);
        if (!$result) throw new Exception('toasts.transport.failed');
        $this->resource->pallets()->update(['transport_id' => null]);
        $this->resource->parcels()->update(['transport_id' => null]);
        return $this->resource;
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $validated = $this->validate();
        $this->updateTimestamps($validated);

        try {
            $transport = match ($this->formStatus()) {
                FormStatus::EDITING => $this->updateTransport($validated),
                FormStatus::CREATING => $this->createTransport($validated),
            };

            $transport->pallets()->saveMany($this->linked_pallets);
            $transport->parcels()->saveMany($this->linked_parcels);

            $this->dispatch('items-updated');
            Flux::toast(variant: 'success', text: __('toasts.transport.saved'));
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" wire:poll.60s class="space-y-6 min-h-full">
    <flux:select variant="listbox" wire:model.live="type" label="{{ __('app.type') }}">
        @foreach (TransportType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="status" label="{{ __('app.status') }}">
        @foreach (TransportStatus::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:field>
        <flux:select wire:model.live="linked_pallets" class="hidden" multiple></flux:select>
        <livewire:fields.scanner-field handles="{{ Pallet::class }}" :items="$linked_pallets" buttonText="{{ __('app.scan.scan_pallets') }}" label="{{ __('app.linked_pallets') }}"
                                :key="'pallets-'.count($linked_pallets)"/>
        <flux:error name="linked_pallets"/>
    </flux:field>

    <flux:field>
        <flux:select wire:model.live="linked_parcels" class="hidden" multiple></flux:select>
        <livewire:fields.scanner-field handles="{{ Parcel::class }}" :items="$linked_parcels" buttonText="{{ __('app.scan.scan_parcels') }}" label="{{ __('app.linked_parcels') }}"
                                :key="'parcels-'.count($linked_parcels)"/>
        <flux:error name="linked_parcels"/>
    </flux:field>

    <flux:textarea wire:model="notes" label="{{ __('app.notes') }}" resize="none"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.save') }}</flux:button>
    </div>
</form>
