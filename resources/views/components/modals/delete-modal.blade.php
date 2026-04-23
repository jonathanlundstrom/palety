<?php

use App\Models\Content;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Recipient;
use App\Models\Transport;
use Flux\Flux;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public ?Model $resource;

    #[Validate('required')]
    public string $confirmation_text = '';

    #[On('confirm-delete')]
    public function delete(int $id, string $class): void {
        $abstract = app()->make($class);
        $this->resource = $abstract::find($id);
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    #[Computed]
    public function confirmed(): bool {
        return $this->confirmation_text === 'DELETE';
    }

    #[Computed]
    public function isFlyout() {
        return !Browser::isDesktop();
    }

    /**
     * Global method to handle deletion of all kinds of content.
     * All relations are set up to cascade, so ideally nothing further is needed.
     *
     * @return void
     */
    public function onSubmit(): void {
        $this->validate(); // For good measure...

        $toast_base = match ($this->resource::class) {
            Recipient::class => 'toasts.recipient',
            Transport::class => 'toasts.transport',
            Content::class => 'toasts.content',
            Parcel::class => 'toasts.parcel',
            Pallet::class => 'toasts.pallet',
        };

        try {
            if ($this->resource->delete()) {
                Flux::toast(variant: 'success', text: __("{$toast_base}.delete.success"));
                $this->dispatch('items-updated');
                $this->dispatch('modal-close');
            } else {
                throw new Exception("{$toast_base}.delete.failed");
            }
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}

?>
<flux:modal name="delete-confirmation" class="w-xs sm:w-10/12 md:w-128" :flyout="$this->isFlyout" position="bottom" x-on:close="$wire.dispatch('reset-modal')">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('modals.delete.title') }}</flux:heading>
            <flux:text class="mt-2">{{ __('modals.delete.subtitle') }}</flux:text>
        </div>

        <form wire:submit="onSubmit" class="space-y-6">
            <flux:input wire:model.live="confirmation_text" placeholder="DELETE" />

            <div class="flex justify-between">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('app.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" :disabled="!$this->confirmed()">{{ __('app.delete') }}</flux:button>
            </div>
        </form>
    </div>
</flux:modal>
