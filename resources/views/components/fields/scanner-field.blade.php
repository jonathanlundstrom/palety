<?php

use App\Models\Pallet;
use App\Models\Parcel;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {

    /**
     * The models which to iterate through.
     * @var array
     */
    public array $items = [];

    /**
     * The label to display above the field.
     * @var string
     */
    public string $label;

    /**
     * The text to display in the trigger button.
     * @var string
     */
    public string $buttonText;

    /**
     * Dispatch the event which initializes the QR code scanner.
     * @return void
     */
    public function scan(): void {
        $this->dispatch('scan');
    }

    /**
     * Undo scanning an item based on ID and class.
     * @param int $id
     * @param string $class
     * @return void
     */
    public function undo(int $id, string $class): void {
        $this->dispatch('undo-scan', id: $id, class: $class);
    }

    /**
     * Get the total weight of the associated items.
     * @return float
     */
    #[Computed]
    public function weight(): float {
        return array_sum(array_map(fn($item) => $item->getWeight(), $this->items));
    }

}

?>
<scanner-field-wrapper data-flux-field>
    <flux:label class="flex items-center justify-between">
        {{ $label }}
        <span class="text-xs whitespace-nowrap flex opacity-50">
            <flux:icon.scale variant="micro" class="mr-1"/>
            {{ $this->weight }} {{ __('app.weight.unit') }}
        </span>
    </flux:label>

    <scanner-field>
        <flux:card class="p-2 bg-gray-50 dark:bg-white/10 rounded-lg border-b-0 rounded-b-none space-y-2">
            @forelse ($items as $item)
                @if ($item::class === Parcel::class)
                    <flux:card class="p-3 space-y-3 rounded-md">
                        <div class="flex gap-3 items-center">
                            <flux:badge color="zinc" size="sm">#{{ $item->id }}</flux:badge>
                            <div class="flex-1">
                                <flux:text class="flex-1">{{ $item->contentList() }} – {{ $item->getWeight() }} {{ __('app.weight.unit') }}</flux:text>
                            </div>
                            <flux:button variant="ghost" icon="trash" color="red" size="xs"
                                         wire:click="undo({{ $item->id }}, '{{ addslashes($item::class) }}')"/>
                        </div>
                    </flux:card>
                @elseif ($item::class === Pallet::class)
                    <flux:card class="p-3 space-y-3 rounded-md">
                        <div class="flex gap-3 items-center">
                            <flux:badge color="zinc" size="sm">#{{ $item->id }}</flux:badge>
                            <div class="flex-1">
                                <flux:text class="flex-1">
                                    {{ $item->contentList() ?: 'N/A' }} – {{ $item->getWeight() }} {{ __('app.weight.unit') }}
                                </flux:text>
                            </div>
                            <flux:button variant="ghost" icon="trash" color="red" size="xs"
                                         wire:click="undo({{ $item->id }}, '{{ addslashes($item::class) }}')"/>
                        </div>
                    </flux:card>
                @endif
            @empty
                <flux:text class="py-4 text-center">{{ __('app.scan.no_items') }}</flux:text>
            @endforelse
        </flux:card>

        <flux:modal.trigger name="scanner-modal">
            <flux:button icon="qr-code" class="rounded-t-none w-full" wire:click="scan">{{ $buttonText }}</flux:button>
        </flux:modal.trigger>
    </scanner-field>
</scanner-field-wrapper>
