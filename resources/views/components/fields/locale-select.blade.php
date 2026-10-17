@props([
    'label' => __('app.locale.label'),
])

<flux:select variant="listbox" :label="$label" {{ $attributes }}>
    <flux:select.option value="sv">
        <div class="flex items-center gap-2">
            <flux:flag country="SE" size="xs" />
            <span>{{ __('app.locales.sv') }}</span>
        </div>
    </flux:select.option>
    <flux:select.option value="en">
        <div class="flex items-center gap-2">
            <flux:flag country="GB" size="xs" />
            <span>{{ __('app.locales.en') }}</span>
        </div>
    </flux:select.option>
    <flux:select.option value="uk">
        <div class="flex items-center gap-2">
            <flux:flag country="UA" size="xs" />
            <span>{{ __('app.locales.uk') }}</span>
        </div>
    </flux:select.option>
</flux:select>
