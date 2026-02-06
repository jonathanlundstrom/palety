<?php

use App\Enumerables\FormStatus;
use App\Enumerables\ImportCategory;
use App\Livewire\Components\FormComponent;
use App\Models\Content;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {
    #[Validate('required')]
    public string $category;

    #[Validate('required')]
    public string $label_en;

    #[Validate('required')]
    public string $label_ua;

    /**
     * Create new content from validated form data.
     * @param array $validated
     * @return void
     */
    protected function createContent(array $validated): void {
        $content = Content::create($validated);
        if (!$content) throw new Exception('toasts.content.failed');
    }

    /**
     * Update existing content with validated form data.
     * @param array $validated
     */
    protected function updateContent(array $validated): void {
        $result = $this->resource->update($validated);
        if (!$result) throw new Exception('toasts.content.failed');
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $validated = $this->validate();

        try {
            match ($this->formStatus()) {
                FormStatus::EDITING => $this->updateContent($validated),
                FormStatus::CREATING => $this->createContent($validated),
            };

            Flux::toast(variant: 'success', text: __('toasts.content.saved'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:input wire:model="label_en" label="{{ __('app.label_en') }}"/>
    <flux:input wire:model="label_ua" label="{{ __('app.label_ua') }}"/>

    <flux:select variant="listbox" wire:model.live="category" label="{{ trans_choice('app.category.label', 1) }}" placeholder="{{ __('app.category.select') }}">
        @foreach (ImportCategory::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
