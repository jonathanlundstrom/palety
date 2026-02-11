<?php

use App\Enumerables\FormStatus;
use App\Enumerables\ImportCategory;
use App\Enumerables\PalletType;
use App\Enumerables\Availability;
use App\Events\PalletSaved;
use App\Livewire\Components\FormComponent;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
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
                if ($object->getAvailability() === Availability::AVAILABLE) {
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

    /**
     * Remove provided resource from the list of scanned items.
     * @param int $id
     * @param string $class
     * @return void
     */
    #[On('undo-scan')]
    public function undoScan(int $id, string $class): void {
        $this->scanned_items = array_filter(
            $this->scanned_items,
            fn($item) => $item->id !== $id
        );
    }

    #[Validate('required')]
    public string $type = PalletType::CALCULATED->name;

    #[Validate('required_if:type,' . PalletType::MANUAL_PALLET->name)]
    public string $category;

    #[Validate('required|integer')]
    public int $recipient_id;

    #[Validate('required_if:type,' . PalletType::CALCULATED->name . '|array')]
    public array $scanned_items = [];

    #[Validate('required_if:type,' . PalletType::MANUAL_PALLET->name)]
    public string $label_en;

    #[Validate('required_if:type,' . PalletType::MANUAL_PALLET->name)]
    public string $label_ua;

    #[Validate('required_if:type,' . PalletType::MANUAL_PALLET->name)]
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
        $validated = [
            ...$this->validate(),
            'user_id' => Auth::id()
        ];

        try {
            $is_creating = $this->formStatus() === FormStatus::CREATING;
            $previous_weight = $is_creating ? null : $this->resource->getWeight();

            DB::transaction(function () use ($validated, $is_creating, $previous_weight) {
                $pallet = match ($this->formStatus()) {
                    FormStatus::EDITING => $this->updatePallet($validated),
                    FormStatus::CREATING => $this->createPallet($validated),
                };

                if ($this->isCalculated()) {
                    $pallet->parcels()->saveMany($this->scanned_items);
                    $pallet->refresh(); // Refresh model after saving relations.
                }

                // Fire event if created or on weight change:
                if ($is_creating || $pallet->getWeight() != $previous_weight) {
                    event(new PalletSaved($pallet));
                }
            });

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
    <flux:select variant="listbox" wire:model.live="type" label="{{ __('app.type') }}">
        @foreach (PalletType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="recipient_id" label="{{ __('app.recipient') }}">
        @foreach ($this->recipients as $recipient)
            <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->isCalculated)
        <flux:field>
            <flux:select wire:model.live="scanned_items" class="hidden" multiple></flux:select>
            <flux:label>{{ __('app.scanned_items') }}</flux:label>
            <livewire:scanner-field :items="$scanned_items" buttonText="{{ __('app.scan.scan_parcels') }}"
                                    :key="'parcels-'.count($scanned_items)"/>
            <flux:error name="scanned_items"/>
        </flux:field>
    @else
        <flux:select variant="listbox" wire:model.live="category" label="{{ trans_choice('app.category.label', 1) }}"
                     placeholder="{{ __('app.category.select') }}">
            @foreach (ImportCategory::cases() as $case)
                <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:input wire:model="label_en" label="{{ __('app.label_en') }}"/>
        <flux:input wire:model="label_ua" label="{{ __('app.label_ua') }}"/>

        <flux:field>
            <flux:label>{{ __('app.weight.label') }}</flux:label>
            <flux:input.group>
                <flux:input type="number" step="0.01" wire:model="weight"/>
                <flux:input.group.suffix>{{ __('app.weight.unit') }}</flux:input.group.suffix>
            </flux:input.group>
            <flux:error name="weight"/>
        </flux:field>
    @endif

    <flux:textarea wire:model="notes" label="{{ __('app.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
