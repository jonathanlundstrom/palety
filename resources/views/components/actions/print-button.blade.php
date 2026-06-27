@php
    use App\Enumerables\PalletStatus;
    use App\Models\Pallet;
    $disabled = $object instanceof Pallet && $object->status === PalletStatus::DRAFT;
@endphp

@props([
    'object' => (object) [],
])

<flux:menu.item icon="printer" :disabled="$disabled" wire:click="print({{ $object->id }}, '{{ addslashes($object::class) }}')">
    {{ __('app.print_label') }}
</flux:menu.item>
