@php
    use App\Enumerables\Availability;
    use App\Enumerables\PalletStatus;
    use App\Models\Pallet;
@endphp
@props([
    'object' => (object) [],
])

@php
    $disabled = is_null($object->recipient_id)
        || $object->getAvailability() !== Availability::AVAILABLE
        || ($object instanceof Pallet && $object->status !== PalletStatus::COMPLETED);
@endphp

<flux:modal.trigger name="add-to-transport">
    <flux:menu.item icon="truck" :disabled="$disabled"
                    wire:click="addToTransport({{ $object->id }}, '{{ addslashes($object::class) }}')">
        {{ __('app.add_to_transport') }}
    </flux:menu.item>
</flux:modal.trigger>
