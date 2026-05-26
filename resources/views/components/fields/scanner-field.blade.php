<?php

use App\Models\Pallet;
use App\Models\Parcel;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public string $handles;

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

    public function mount(array $items = []): void {
        $this->items = $items;
    }

    /**
     * Dispatch the event which sets the correct type for adding
     * manual resources to the list of scanned items.
     *
     * @return void
     */
    public function add(): void {
        $this->dispatch('add-resource', type: $this->handles);
    }

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
        <span>
            {{ $label }}
        </span>
        <span class="whitespace-nowrap flex gap-1">
            <flux:badge color="zinc" size="sm" inset="top bottom">{{ count($items) }}</flux:badge>

            <flux:badge color="zinc" size="sm" inset="top bottom" icon="scale">
                {{ $this->weight }} {{ __('app.weight.unit') }}
            </flux:badge>
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
                                <flux:text class="flex-1">{{ $item->contentList() }}
                                    – {{ $item->getWeight() }} {{ __('app.weight.unit') }}</flux:text>
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
                                    {{ $item->contentList() ?: 'N/A' }}
                                    – {{ $item->getWeight() }} {{ __('app.weight.unit') }}
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

        <flux:button.group>
            <flux:modal.trigger name="scanner-modal">
                <flux:button icon="qr-code" class="rounded-t-none w-full"
                             wire:click="scan">{{ $buttonText }}</flux:button>
            </flux:modal.trigger>

            <flux:dropdown>
                <flux:button icon="chevron-down" class="rounded-t-none"></flux:button>
                <flux:menu>
                    <flux:modal.trigger name="add-modal">
                        <flux:menu.item icon="magnifying-glass" wire:click="add()">Add manually</flux:menu.item>
                    </flux:modal.trigger>
                </flux:menu>
            </flux:dropdown>
        </flux:button.group>
    </scanner-field>
</scanner-field-wrapper>
