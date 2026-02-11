<?php

use Livewire\Component;

new class extends Component {
    //
}
?>
<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('pages.settings.subpages.appearance.headline')" :subheading="__('pages.settings.subpages.appearance.subtitle')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('pages.settings.subpages.appearance.extras.light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('pages.settings.subpages.appearance.extras.dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('pages.settings.subpages.appearance.extras.system') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
