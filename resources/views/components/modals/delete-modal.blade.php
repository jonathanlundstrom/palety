<?php

use App\Enumerables\Permission;
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
        $this->resource = $class::findOrFail($id);
    }

    /**
     * Resolve the permission required to delete the given model class.
     * Classes that are not listed cannot be deleted through this modal.
     *
     * @param string $class
     * @return Permission
     */
    private function permissionFor(string $class): Permission {
        return match ($class) {
            Parcel::class => Permission::DELETE_PARCEL,
            Pallet::class => Permission::DELETE_PALLET,
            Transport::class => Permission::DELETE_TRANSPORT,
            Content::class => Permission::DELETE_CONTENT,
            Recipient::class => Permission::DELETE_RECIPIENT,
        };
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
        $this->authorize($this->permissionFor($this->resource::class));
        $this->validate(); // For good measure...
        $resource_type = strtolower(class_basename($this->resource));

        try {
            if ($this->resource->delete()) {
                Flux::toast(variant: 'success', text: __("toasts.{$resource_type}.delete.success"));
                $this->dispatch('items-updated');
                $this->dispatch('modal-close');
            } else {
                throw new Exception("toasts.{$resource_type}.delete.failed");
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
