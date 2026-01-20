<?php

use App\Enumerables\ImportCategory;
use App\Livewire\Components\FormComponent;
use App\Models\Content;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[Locked]
    public Content $content;

    #[On('edit-resource')]
    public function edit(int $id): void {
        $this->content = Content::find($id);
        $this->hydrateFields($this->content);
    }

    #[Validate('required')]
    public string $category;

    #[Validate('required')]
    public string $label_en;

    #[Validate('required')]
    public string $label_ua;

    public function onSubmit() {
        $validated = $this->validate();

        try {
            if (isset($this->content) && $this->content->exists) {
                if (!$this->content->update($validated)) {
                    throw new Exception('toasts.content.failed');
                }
            } else {
                if (!Content::create($validated)) {
                    throw new Exception('toasts.content.failed');
                }
            }

            Flux::toast(variant: 'success', text: __('toasts.content.saved'));

            $this->dispatch('content-updated');
            $this->dispatch('modal-close', name: 'content-form');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:input wire:model="label_en" label="{{ __('validation.attributes.label_en') }}"/>
    <flux:input wire:model="label_ua" label="{{ __('validation.attributes.label_ua') }}"/>

    <flux:select variant="listbox" wire:model.live="category" label="{{ __('validation.attributes.category') }}">
        @foreach (ImportCategory::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
