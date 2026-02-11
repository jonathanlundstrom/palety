<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component {
    public string $current_password = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');
            throw $e;
        }

        $user_updated = Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        if ($user_updated) {
            $this->reset('current_password', 'password', 'password_confirmation');
            Flux::toast(variant: 'success', text: __('toasts.user.password.saved'));
        } else {
            Flux::toast(variant: 'danger', text: __('toasts.user.password.failed'));
        }

    }
}
?>
<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('pages.settings.subpages.password.headline')" :subheading="__('pages.settings.subpages.password.subtitle')">
        <form wire:submit="updatePassword" class="mt-6 space-y-6">
            <flux:input
                wire:model="current_password"
                :label="__('app.current_password')"
                type="password"
                required
                autocomplete="current-password"
            />
            <flux:input
                wire:model="password"
                :label="__('app.new_password')"
                type="password"
                required
                autocomplete="new-password"
            />
            <flux:input
                wire:model="password_confirmation"
                :label="__('app.confirm_password')"
                type="password"
                required
                autocomplete="new-password"
            />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('app.save') }}</flux:button>
                </div>
            </div>
        </form>
    </x-settings.layout>
</section>
