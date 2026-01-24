<?php

use App\Enumerables\ParcelType;
use App\Livewire\Components\FormComponent;
use App\Models\Content;
use App\Models\Parcel;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[Locked]
    public Parcel $parcel;

    #[On('edit-resource')]
    public function edit(int $id): void {
        $this->parcel = Parcel::find($id);
        $this->hydrateFields($this->parcel);
    }

    #[Validate('required')]
    public string $type = ParcelType::BOX->name;

    #[Validate('required')]
    public array $content;

    #[Validate('required')]
    public string $weight;

    #[Validate('nullable')]
    public string $notes;

    #[Computed]
    protected function contentItems(): Collection {
        return Content::query()
            ->select('id', Content::label())
            ->orderBy(Content::label())
            ->get();
    }

    public function onSubmit() {
        $validated = $this->validate();

        try {
            if (isset($this->parcel) && $this->parcel->exists) {
                if ($this->parcel->update($validated)) {
                    $this->parcel->content()->sync($this->content);
                } else {
                    throw new Exception('toasts.parcel.failed');
                }
            } else {
                if ($parcel = Parcel::create($validated)) {
                    $parcel->content()->sync($this->content);
                } else {
                    throw new Exception('toasts.parcel.failed');
                }
            }

            Flux::toast(variant: 'success', text: __('toasts.parcel.saved'));

            $this->dispatch('content-updated');
            $this->dispatch('modal-close', name: 'parcels-form');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:select variant="listbox" wire:model.live="type" label="{{ __('validation.attributes.type') }}">
        @foreach (ParcelType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:pillbox variant="combobox" wire:model.live="content" label="{{ __('validation.attributes.content') }}"
                  placeholder="{{ __('app.content.select') }}" multiple>
        @foreach ($this->contentItems as $item)
            <flux:select.option value="{{ $item->id }}">{{ $item->{Content::label()} }}</flux:select.option>
        @endforeach
    </flux:pillbox>

    <flux:field>
        <flux:label>{{ __('validation.attributes.weight') }}</flux:label>
        <flux:input.group>
            <flux:input type="number" step="0.01" wire:model="weight" />
            <flux:input.group.suffix>{{ __('app.weight.unit') }}</flux:input.group.suffix>
        </flux:input.group>
        <flux:error name="weight" />
    </flux:field>

    <flux:textarea wire:model="notes" label="{{ __('validation.attributes.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
