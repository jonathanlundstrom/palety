<?php

use App\Enumerables\Availability;
use App\Enumerables\PalletStatus;
use App\Enumerables\TransportStatus;
use App\Models\Pallet;
use App\Models\Parcel;
use App\Models\Transport;
use Flux\Flux;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public ?int $resource_id = null;

    #[Locked]
    public ?string $resource_class = null;

    #[Validate('required|integer')]
    public ?int $transport_id = null;

    #[On('confirm-add-to-transport')]
    public function prepare(int $id, string $class): void {
        $this->resource_id = $id;
        $this->resource_class = $class;
        $this->transport_id = $this->transports->first()?->id;
        $this->resetValidation();
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    #[Computed]
    public function resource(): ?Model {
        return in_array($this->resource_class, [Parcel::class, Pallet::class], true)
            ? ($this->resource_class)::find($this->resource_id)
            : null;
    }

    #[Computed]
    public function resourceHeading(): ?string {
        if (! $this->resource) {
            return null;
        }

        return __('modals.add_to_transport.source_heading', [
            'label' => trans_choice('app.'.mb_strtolower(class_basename($this->resource)), 1),
            'id' => $this->resource->id,
            'recipient' => $this->resource->recipient?->name ?? '--',
        ]);
    }

    /**
     * Get the transports that are still being loaded, and can
     * therefore receive additional parcels and pallets.
     */
    #[Computed]
    public function transports(): Collection {
        return Transport::query()
            ->where('status', TransportStatus::IN_PROGRESS)
            ->withCount(['pallets', 'parcels'])
            ->orderByDesc('id')
            ->get();
    }

    #[Computed]
    public function isFlyout(): bool {
        return !Browser::isDesktop();
    }

    /**
     * Load the selected parcel or pallet onto the chosen transport.
     * The availability is checked again on submit, in case the resource
     * was loaded elsewhere while the modal was open.
     *
     * @return void
     */
    public function onSubmit(): void {
        $this->validate();

        $resource = $this->resource;
        $toast_key = 'toasts.'.mb_strtolower(class_basename($resource ?? Parcel::class));

        try {
            if (is_null($resource)) {
                throw new Exception($toast_key.'.not_found');
            }

            if ($resource->getAvailability() !== Availability::AVAILABLE) {
                throw new Exception($toast_key.'.loaded');
            }

            if (is_null($resource->recipient)) {
                throw new Exception($toast_key.'.no_recipient');
            }

            if ($resource instanceof Pallet && $resource->status !== PalletStatus::COMPLETED) {
                throw new Exception($toast_key.'.draft');
            }

            $transport = Transport::query()
                ->where('status', TransportStatus::IN_PROGRESS)
                ->find($this->transport_id);

            if (is_null($transport)) {
                throw new Exception($toast_key.'.transport.failed');
            }

            $relation = $resource instanceof Pallet ? 'pallets' : 'parcels';
            $transport->{$relation}()->save($resource);

            Flux::toast(variant: 'success', text: __($toast_key.'.transport.success'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}

?>
<flux:modal name="add-to-transport" class="w-xs sm:w-10/12 md:w-128" :flyout="$this->isFlyout" position="bottom"
            x-on:close="$wire.dispatch('reset-modal')">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('modals.add_to_transport.title') }}</flux:heading>
            <flux:text class="mt-2">{{ __('modals.add_to_transport.subtitle') }}</flux:text>
        </div>

        <form wire:submit="onSubmit" class="space-y-6">
            @if ($this->resource)
                <flux:callout variant="secondary" icon="archive-box" :heading="$this->resourceHeading"/>
            @else
                <flux:skeleton animate="shimmer" class="size-full rounded-lg" style="height: 3.375rem"/>
            @endif

            @if ($this->transports->isEmpty())
                <flux:callout variant="warning" icon="information-circle"
                              :heading="__('modals.add_to_transport.empty')"/>
            @else
                <flux:select variant="listbox" wire:model.live="transport_id"
                             :label="__('modals.add_to_transport.target_label')">
                    @foreach ($this->transports as $transport)
                        <flux:select.option :value="$transport->id" wire:key="{{ $transport->id }}">
                            #{{ $transport->id }} – {{ $transport->type->label() }}
                            ({{ $transport->pallets_count }} {{ mb_strtolower(trans_choice('app.pallet', $transport->pallets_count)) }},
                            {{ $transport->parcels_count }} {{ mb_strtolower(trans_choice('app.parcel', $transport->parcels_count)) }})
                        </flux:select.option>
                    @endforeach
                </flux:select>
            @endif

            <div class="flex justify-between">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('app.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" :disabled="!$transport_id">
                    {{ __('modals.add_to_transport.confirm') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
