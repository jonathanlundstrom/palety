<?php

use App\Enumerables\FormStatus;
use App\Enumerables\PalletStatus;
use App\Enumerables\PalletType;
use App\Enumerables\Availability;
use App\Events\PalletSaved;
use App\Livewire\Components\FormComponent;
use App\Models\Content;
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
        $this->linked_parcels = $this->resource->parcels->all();
    }

    #[On('scan-result')]
    public function onScanResult(?array $payload): void {
        if (!is_null($payload) && $payload['class'] === Parcel::class) {
            try {
                $abstract = app()->make($payload['class']);
                $object = $abstract::find($payload['id']);
                if ($object->getAvailability() === Availability::AVAILABLE) {
                    if (!in_array($object->id, array_column($this->linked_parcels, 'id'), true)) {
                        $this->linked_parcels[] = $object;
                        Flux::toast(variant: 'success', text: __('toasts.parcel.added'));
                        $this->dispatch('vibrate-success');
                    } else {
                        Flux::toast(variant: 'warning', text: __('toasts.parcel.already_added'));
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
        $this->linked_parcels = array_filter(
            $this->linked_parcels,
            fn($item) => $item->id !== $id
        );
    }

    #[Validate('required')]
    public string $type = PalletType::CALCULATED->name;

    #[Validate('required')]
    public string $status = PalletStatus::DRAFT->name;

    #[Validate('required|integer')]
    public int $recipient_id;

    #[Validate('required_if:type,' . PalletType::CALCULATED->name . '|array')]
    public array $linked_parcels = [];

    #[Validate('required_if:type,' . PalletType::MANUAL_PALLET->name . '|array')]
    public array $content = [];

    #[Validate('required_if:type,' . PalletType::MANUAL_PALLET->name)]
    public string $weight;

    #[Validate('nullable')]
    public string $notes;

    #[Computed]
    protected function isCalculated(): bool {
        return $this->type === PalletType::CALCULATED->name;
    }

    #[Computed]
    protected function contentItems(): Collection {
        return Content::query()
            ->select('id', Content::label())
            ->orderBy(Content::label())
            ->get();
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
            $previous_status = $is_creating ? null : $this->resource->status;

            DB::transaction(function () use ($validated, $is_creating, $previous_weight, $previous_status) {
                $pallet = match ($this->formStatus()) {
                    FormStatus::EDITING => $this->updatePallet($validated),
                    FormStatus::CREATING => $this->createPallet($validated),
                };

                if ($this->isCalculated()) {
                    $pallet->content()->detach();
                    $pallet->parcels()->saveMany($this->linked_parcels);
                    $pallet->refresh(); // Refresh model after saving relations.
                } else {
                    $pallet->content()->sync($this->content);
                }

                $created_as_completed = $is_creating && $pallet->status === PalletStatus::COMPLETED;
                $status_changed_to_completed = !$is_creating && $previous_status === PalletStatus::DRAFT && $pallet->status === PalletStatus::COMPLETED;
                $weight_changed_while_completed = !$is_creating && $pallet->status === PalletStatus::COMPLETED && $pallet->getWeight() != $previous_weight;

                if ($created_as_completed || $status_changed_to_completed || $weight_changed_while_completed) {
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
<form wire:submit="onSubmit" wire:poll.60s class="space-y-6 min-h-full">
    @if ($this->formStatus() === FormStatus::EDITING && $resource->getAvailability() === Availability::ALREADY_LOADED)
        <flux:callout variant="danger" heading="{!! __('app.pallet_loaded') !!}" icon="exclamation-circle"/>
    @endif

    <flux:select variant="listbox" wire:model.live="type" label="{{ __('app.type') }}">
        @foreach (PalletType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="status" label="{{ __('app.status') }}">
        @foreach (PalletStatus::cases() as $case)
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
            <flux:select wire:model.live="linked_parcels" class="hidden" multiple></flux:select>
            <livewire:fields.scanner-field handles="{{ Parcel::class }}" :items="$linked_parcels"
                                           buttonText="{{ __('app.scan.scan_parcels') }}"
                                           label="{{ __('app.linked_parcels') }}"
                                           :key="'parcels-'.count($linked_parcels)"/>
            <flux:error name="linked_parcels"/>
        </flux:field>
    @else
        <flux:pillbox variant="combobox" wire:model.live="content" label="{{ __('app.content.label') }}"
                      placeholder="{{ __('app.content.select') }}" multiple>
            @foreach ($this->contentItems as $item)
                <flux:select.option value="{{ $item->id }}">{{ $item->{Content::label()} }}</flux:select.option>
            @endforeach
        </flux:pillbox>

        <flux:field>
            <flux:label>{{ __('app.weight.label') }}</flux:label>
            <flux:input.group>
                <flux:input type="number" lang="en_EN" step="0.01" wire:model="weight"/>
                <flux:input.group.suffix>{{ __('app.weight.unit') }}</flux:input.group.suffix>
            </flux:input.group>
            <flux:error name="weight"/>
        </flux:field>
    @endif

    <flux:textarea wire:model="notes" label="{{ __('app.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.save') }}</flux:button>
    </div>
</form>
