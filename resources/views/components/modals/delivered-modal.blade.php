<?php

use App\Enumerables\TransportStatus;
use App\Models\Transport;
use Carbon\Carbon;
use Flux\Flux;
use hisorange\BrowserDetect\Facade as Browser;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public ?int $transport_id = null;

    #[Validate('required|date_format:Y-m-d')]
    public ?string $delivered_on = null;

    #[On('confirm-delivered')]
    public function prepare(int $id): void {
        $this->transport_id = $id;
        $this->delivered_on = Carbon::now()->format('Y-m-d');
        $this->resetValidation();
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    #[Computed]
    public function transport(): ?Transport {
        return $this->transport_id
            ? Transport::find($this->transport_id)
            : null;
    }

    #[Computed]
    public function transportHeading(): ?string {
        if (! $this->transport) {
            return null;
        }

        return __('modals.delivered.source_heading', [
            'id' => $this->transport->id,
            'sent' => $this->transport->sent_at?->format('Y-m-d') ?? '--',
        ]);
    }

    #[Computed]
    public function isFlyout(): bool {
        return !Browser::isDesktop();
    }

    /**
     * Mark the selected transport as delivered on the chosen date.
     * The transport keeps its original sent date, unless it is missing
     * for some reason, in which case the delivery date is used.
     *
     * @return void
     */
    public function onSubmit(): void {
        $this->validate();

        try {
            $transport = Transport::findOrFail($this->transport_id);
            $delivered_at = Carbon::parse($this->delivered_on)->startOfDay();

            $result = $transport->update([
                'status' => TransportStatus::DELIVERED,
                'sent_at' => $transport->sent_at ?? $delivered_at,
                'delivered_at' => $delivered_at,
            ]);

            if (!$result) {
                throw new Exception('toasts.transport.delivered.failed');
            }

            Flux::toast(variant: 'success', text: __('toasts.transport.delivered.success'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception) {
            Flux::toast(variant: 'danger', text: __('toasts.transport.delivered.failed'));
        }
    }

}

?>
<flux:modal name="mark-delivered" class="w-xs sm:w-10/12 md:w-128" :flyout="$this->isFlyout" position="bottom"
            x-on:close="$wire.dispatch('reset-modal')">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('modals.delivered.title') }}</flux:heading>
            <flux:text class="mt-2">{{ __('modals.delivered.subtitle') }}</flux:text>
        </div>

        <form wire:submit="onSubmit" class="space-y-6">
            @if ($this->transport)
                <flux:callout variant="secondary" icon="truck" :heading="$this->transportHeading"/>
            @else
                <flux:skeleton animate="shimmer" class="size-full rounded-lg" style="height: 3.375rem"/>
            @endif

            <flux:date-picker wire:model="delivered_on" locale="{{ App::getLocale() }}"
                              :label="__('modals.delivered.date_label')" with-today week-numbers/>

            <div class="flex justify-between">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('app.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="primary" :disabled="!$delivered_on">
                    {{ __('modals.delivered.confirm') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
