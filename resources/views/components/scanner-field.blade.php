<?php

use App\Models\Pallet;
use App\Models\Parcel;
use Livewire\Component;

new class extends Component {

    /**
     * The models which to iterate through.
     * @var array
     */
    public array $items = [];

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
     *
     * @param int $id
     * @param string $class
     * @return void
     */
    public function undo(int $id, string $class): void {
        $this->dispatch('undo-scan', id: $id, class: $class);
    }

}

?>
<scanner-field>
    <flux:card class="p-2 bg-gray-50 dark:bg-white/10 rounded-lg border-b-0 rounded-b-none space-y-1">
        @forelse ($items as $item)
            @if ($item::class === Parcel::class)
                <flux:card class="p-3 space-y-3 rounded-md">
                    <div class="flex gap-3 items-center">
                        <flux:badge color="zinc" size="sm">#{{ $item->id }}</flux:badge>
                        <div class="flex-1">
                            <flux:text class="flex-1">{{ $item->contentList() }}</flux:text>
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
                                {{ $item->{$item::label()} ?: 'N/A' }}
                                ({{ $item->getWeight() }} {{ __('app.weight.unit') }})
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
