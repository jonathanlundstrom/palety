@php use App\Enumerables\TransportStatus; @endphp
@props([
    'object' => (object) [],
])

@if ($object->status === TransportStatus::SENT)
    <flux:modal.trigger name="mark-delivered">
        <flux:menu.item icon="check-circle" wire:click="delivered({{ $object->id }})">
            {{ __('app.mark_delivered') }}
        </flux:menu.item>
    </flux:modal.trigger>
@endif
