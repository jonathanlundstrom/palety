@php
    use App\Models\Content;
    $disabled = $object instanceof Content && $object->usage_count > 0;
@endphp

@props([
    'object' => (object) [],
])

@if ($disabled)
    <flux:menu.item icon="trash" variant="danger" disabled>{{ __('app.delete') }}</flux:menu.item>
@else
    <flux:modal.trigger name="delete-confirmation">
        <flux:menu.item icon="trash" variant="danger" wire:click="delete({{ $object->id }}, '{{ addslashes($object::class) }}')">{{ __('app.delete') }}</flux:menu.item>
    </flux:modal.trigger>
@endif
