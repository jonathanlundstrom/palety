<?php

use App\Enumerables\PalletType;
use App\Enumerables\ParcelType;
use App\Livewire\Components\FormComponent;
use App\Models\Content;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[Locked]
    public Pallet $pallet;

    #[On('edit-resource')]
    public function edit(int $id): void {
        $this->pallet = Pallet::find($id);
        $this->hydrateFields($this->pallet);
    }

    #[Validate('required|integer')]
    public int $recipient_id;

    #[Validate('required')]
    public string $type = PalletType::CALCULATED->name;

    #[Validate('required_if:type,' . PalletType::MANUAL_OVERRIDE->name)]
    public array $label_en;

    #[Validate('required_if:type,' . PalletType::MANUAL_OVERRIDE->name)]
    public array $label_ua;

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
        return Recipient::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }

    public function onSubmit() {
        $validated = $this->validate();

        try {
            if (isset($this->pallet) && $this->parcel->pallet) {
                if ($this->pallet->update($validated)) {
                    // $this->parcel->content()->sync($this->content);
                } else {
                    throw new Exception('toasts.pallet.failed');
                }
            } else {
                if ($pallet = Pallet::create($validated)) {
                    //$parcel->content()->sync($this->content);
                } else {
                    throw new Exception('toasts.pallet.failed');
                }
            }

            Flux::toast(variant: 'success', text: __('toasts.pallet.saved'));

            $this->dispatch('content-updated');
            $this->dispatch('modal-close', name: 'pallet-form');
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

    @if ($this->isCalculated)
        <flux:modal.trigger name="scanner-modal">
            <flux:button wire:click="scan">Scan QR-code</flux:button>
        </flux:modal.trigger>
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

    <livewire:scanner-modal/>
</form>
