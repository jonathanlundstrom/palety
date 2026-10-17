<?php

use App\Enumerables\FormStatus;
use App\Enumerables\Availability;
use App\Enumerables\PalletStatus;
use App\Enumerables\Permission;
use App\Enumerables\TransportStatus;
use App\Enumerables\TransportType;
use App\Livewire\Components\FormComponent;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Transport;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {
    /**
     * The model managed by this form. Edit and duplicate requests
     * for any other class are rejected by the FormComponent.
     */
    protected string $model = Transport::class;

    #[On('edit-resource')]
    public function edit(int $id, string $class): void {
        parent::edit($id, $class);
        $this->linked_pallets = $this->resource->pallets->all();
        $this->linked_parcels = $this->resource->parcels->all();
    }

    /**
     * Whether the transport being edited is locked, which freezes its pallets and parcels.
     * @return bool
     */
    #[Computed]
    public function isLocked(): bool {
        return $this->resource?->isLocked() ?? false;
    }

    #[On('scan-result')]
    public function onScanResult(?array $payload): void {
        if (!is_null($payload) && in_array($payload['class'], [Parcel::class, Pallet::class])) {
            [$target, $toast_key] = match ($payload['class']) {
                Parcel::class => ['linked_parcels', 'toasts.parcel'],
                Pallet::class => ['linked_pallets', 'toasts.pallet'],
            };

            try {
                $object = $payload['class']::query()
                    ->when($payload['class'] === Pallet::class, fn($q) => $q->with('parcels'))
                    ->findOrFail($payload['id']);

                if ($object->getAvailability() === Availability::AVAILABLE) {
                    if (!in_array($object->id, array_column($this->{$target}, 'id'), true)) {
                        if (is_null($object->recipient)) {
                            Flux::toast(variant: 'warning', text: __($toast_key . '.no_recipient'));
                        } elseif ($object instanceof Pallet && $object->status !== PalletStatus::COMPLETED) {
                            Flux::toast(variant: 'warning', text: __($toast_key . '.draft'));
                        } else {
                            $this->{$target}[] = $object;
                            Flux::toast(variant: 'success', text: __($toast_key . '.added'));
                            $this->dispatch('vibrate-success');
                        }
                    } else {
                        Flux::toast(variant: 'warning', text: __($toast_key . '.already_added'));
                    }
                } else {
                    Flux::toast(variant: 'danger', text: __($toast_key . '.loaded'));
                }
            } catch (ModelNotFoundException) {
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
     * Existing timestamps are kept, so correcting a sent or delivered transport
     * does not move its dates. Stepping backwards in progress clears the later ones.
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
                'sent_at' => $this->resource?->sent_at ?? Carbon::now(),
                'delivered_at' => null,
            ],
            TransportStatus::DELIVERED->name => [
                ...$validated,
                'sent_at' => $this->resource?->sent_at ?? Carbon::now(),
                'delivered_at' => $this->resource?->delivered_at ?? Carbon::now(),
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
        return $this->resource;
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $this->authorize(match ($this->formStatus()) {
            FormStatus::CREATING => Permission::CREATE_TRANSPORT,
            FormStatus::EDITING => Permission::EDIT_TRANSPORT,
        });

        // Determined before saving, since this save may change the status.
        $isLocked = $this->resource?->isLocked() ?? false;

        // Once locked, a transport may only be edited by those who may also unlock it.
        if ($isLocked) $this->authorize(Permission::UNLOCK_TRANSPORT);

        $validated = $this->validate();
        $this->updateTimestamps($validated);

        try {
            $transport = match ($this->formStatus()) {
                FormStatus::EDITING => $this->updateTransport($validated),
                FormStatus::CREATING => $this->createTransport($validated),
            };

            // The loaded pallets and parcels stay untouched while the transport is locked.
            if (!$isLocked) {
                $transport->pallets()->update(['transport_id' => null]);
                $transport->parcels()->update(['transport_id' => null]);
                $transport->pallets()->saveMany($this->linked_pallets);
                $transport->parcels()->saveMany($this->linked_parcels);
            }

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
    <x-form-heading :title="__('pages.transports.form.'.$this->formMode.'.title')"
                    :subtitle="__('pages.transports.form.'.$this->formMode.'.subtitle')"/>

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
                                :disabled="$this->isLocked" :key="'pallets-'.count($linked_pallets)"/>
        <flux:error name="linked_pallets"/>
    </flux:field>

    <flux:field>
        <flux:select wire:model.live="linked_parcels" class="hidden" multiple></flux:select>
        <livewire:fields.scanner-field handles="{{ Parcel::class }}" :items="$linked_parcels" buttonText="{{ __('app.scan.scan_parcels') }}" label="{{ __('app.linked_parcels') }}"
                                :disabled="$this->isLocked" :key="'parcels-'.count($linked_parcels)"/>
        <flux:error name="linked_parcels"/>
    </flux:field>

    <flux:textarea wire:model="notes" label="{{ __('app.notes') }}" resize="none"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ $this->formActionLabel }}</flux:button>
    </div>
</form>
