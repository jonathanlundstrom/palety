<?php

use Flux\Flux;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use function PHPUnit\Framework\isType;

new class extends Component {

    /**
     * The model type to query.
     */
    public ?string $abstract = null;

    #[Validate('required')]
    public array $resources = [];
    public string $search = '';

    #[On('add-resource')]
    public function add(string $type): void {
        $this->abstract = $type;
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    #[Computed]
    public function items() {
        if (isset($this->abstract)) {
            return ($this->abstract)::query()
                ->available()
                ->when($this->search, fn($query) => $query->where('id', 'like', '%' . $this->search . '%'))
                ->orderBy('id', 'asc')
                ->limit(20)
                ->get()
                ->when(blank($this->search) && $this->resources, function ($results) {
                    return ($this->abstract)::query()
                        ->available()
                        ->whereIn('id', $this->resources)
                        ->whereNotIn('id', $results->pluck('id'))
                        ->get()->merge($results);
                });
        } else {
            return collect();
        }
    }

    #[Computed]
    public function selected(): bool {
        return isset($this->resources) && count($this->resources) > 0;
    }

    /**
     * Handle adding the manually selected resources using
     * existing events originally created for scanning QR-codes.
     *
     * @return void
     */
    public function onSubmit(): void {
        $this->validate(); // For good measure...

        if (isset($this->resources)) {
            foreach ($this->resources as $id) {
                $this->dispatch('scan-result', payload: [
                    'class' => $this->abstract,
                    'id' => $id,
                ]);
            }

            Flux::modal('add-modal')->close();
        }
    }

}

?>
<flux:modal name="add-modal" class="w-xs sm:w-10/12 md:w-128" x-on:close="$wire.dispatchTo('modals.add-modal', 'reset-modal')">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('app.add_modal.title') }}</flux:heading>
            <flux:text class="mt-2">{{ __('app.add_modal.subtitle') }}</flux:text>
        </div>

        <form wire:submit="onSubmit" class="space-y-6">
            <flux:field>
                <flux:pillbox wire:model.live="resources" variant="combobox" multiple :filter="false">
                    <x-slot name="input">
                        <flux:pillbox.input wire:model.live="search" placeholder="{{ __('app.add_modal.placeholder') }}"/>
                    </x-slot>

                    @foreach ($this->items as $item)
                        <flux:pillbox.option :value="$item->id" wire:key="{{ $item->id }}">
                            {{ $item->id }} – {{ $item->contentList() }}
                        </flux:pillbox.option>
                    @endforeach
                </flux:pillbox>

                <flux:error name="resources" />
            </flux:field>

            <div class="flex justify-between">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('app.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" :disabled="!$this->selected()">{{ __('app.add') }}</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
