<?php

use App\Enumerables\FormStatus;
use App\Enumerables\PalletType;
use App\Enumerables\ParcelStatus;
use App\Livewire\Components\FormComponent;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[On('edit-resource')]
    public function edit(int $id, string $class): void {
        parent::edit($id, $class);
        $this->scanned_items = $this->resource->parcels->all();
    }

    #[On('scan-result')]
    public function onScanResult(?array $payload): void {
        if (!is_null($payload) && $payload['class'] === Parcel::class) {
            try {
                $abstract = app()->make($payload['class']);
                $object = $abstract::find($payload['id']);
                if ($object->getAvailability() === ParcelStatus::AVAILABLE) {
                    if (!in_array($object->id, array_column($this->scanned_items, 'id'), true)) {
                        $this->scanned_items[] = $object;
                        Flux::toast(variant: 'success', text: __('toasts.parcel.scanned'));
                    }
                } else {
                    Flux::toast(variant: 'danger', text: __('toasts.parcel.loaded'));
                }
            } catch (QueryException) {
                Flux::toast(variant: 'danger', text: __('toasts.parcel.not_found'));
            }
        }
    }

    #[Validate('required')]
    public string $type = PalletType::CALCULATED->name;

    #[Validate('required|integer')]
    public int $recipient_id;

    #[Validate('required_if:type,' . PalletType::CALCULATED->name . '|array')]
    public array $scanned_items = [];

    #[Validate('required_if:type,' . PalletType::MANUAL_OVERRIDE->name)]
    public string $label_en;

    #[Validate('required_if:type,' . PalletType::MANUAL_OVERRIDE->name)]
    public string $label_ua;

    #[Validate('required_if:type,' . PalletType::MANUAL_OVERRIDE->name)]
    public string $weight;

    #[Validate('nullable')]
    public string $notes;

    #[Computed]
    protected function isCalculated(): bool {
        return $this->type === PalletType::CALCULATED->name;
    }

    #[Computed]
    protected function recipients(): Collection {
        return Recipient::list(['id', 'name'], 'name')->get();
    }

    /**
     * Remove provided resource from the list of scanned items.
     * @param int $resource_id
     * @return void
     */
    public function undoScan(int $resource_id): void {
        $this->scanned_items = array_filter(
            $this->scanned_items,
            fn($item) => $item->id !== $resource_id
        );
    }

    /**
     * Create a new pallet from validated form data.
     * @param array $validated
     * @return Pallet
     */
    protected function createPallet(array $validated): Pallet {
        $pallet = Pallet::create($validated);
        if (!$pallet) throw new Exception('toasts.pallet.failed');
        return $pallet;
    }

    /**
     * Update an existing pallet with validated form data.
     * @param array $validated
     * @return Pallet
     */
    protected function updatePallet(array $validated): Pallet {
        $result = $this->resource->update($validated);
        if (!$result) throw new Exception('toasts.pallet.failed');
        $this->resource->parcels()->update(['pallet_id' => null]);
        return $this->resource;
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $validated = Arr::except($this->validate(), [
            'scanned_items' // Remove in order not to pass to create and update methods.
        ]);

        try {
            $pallet = match ($this->formStatus()) {
                FormStatus::EDITING => $this->updatePallet($validated),
                FormStatus::CREATING => $this->createPallet($validated),
            };

            if ($this->isCalculated()) {
                $pallet->parcels()->saveMany($this->scanned_items);
            }

            Flux::toast(variant: 'success', text: __('toasts.pallet.saved'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:select variant="listbox" wire:model.live="type" label="{{ __('validation.attributes.type') }}">
        @foreach (PalletType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="recipient_id" label="{{ __('validation.attributes.recipient') }}">
        @foreach ($this->recipients as $recipient)
            <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->isCalculated)
        <flux:field>
            <flux:label>{{ __('validation.attributes.scanned_items') }}</flux:label>
            <flux:select wire:model.live="scanned_items" class="hidden" multiple></flux:select>

            <div>
                <flux:card class="p-2 bg-gray-50 rounded-lg border-b-0 rounded-b-none">
                    @forelse ($scanned_items as $parcel)
                        <flux:card class="p-3 space-y-3 rounded-md">
                            <div class="flex gap-3 items-center">
                                <flux:badge color="zinc" size="sm">#{{ $parcel->id }}</flux:badge>
                                <div class="flex-1">
                                    <flux:text class="flex-1">{{ $parcel->contentList() }}</flux:text>
                                </div>
                                <flux:button variant="ghost" icon="trash" color="red" size="xs"
                                             wire:click="undoScan({{ $parcel->id }})"/>
                            </div>
                        </flux:card>
                    @empty
                        <flux:text class="py-4 text-center">No items have been scanned</flux:text>
                    @endforelse
                </flux:card>

                <flux:modal.trigger name="scanner-modal">
                    <flux:button icon="qr-code" class="rounded-t-none w-full" wire:click="scan">Scan parcels
                    </flux:button>
                </flux:modal.trigger>
            </div>

            <flux:error name="scanned_items"/>
        </flux:field>
    @else
        <flux:input wire:model="label_en" label="{{ __('validation.attributes.label_en') }}"/>
        <flux:input wire:model="label_ua" label="{{ __('validation.attributes.label_ua') }}"/>

        <flux:field>
            <flux:label>{{ __('validation.attributes.weight') }}</flux:label>
            <flux:input.group>
                <flux:input type="number" step="0.01" wire:model="weight"/>
                <flux:input.group.suffix>{{ __('app.weight.unit') }}</flux:input.group.suffix>
            </flux:input.group>
            <flux:error name="weight"/>
        </flux:field>
    @endif

    <flux:textarea wire:model="notes" label="{{ __('validation.attributes.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
