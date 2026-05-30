<?php

use Illuminate\Support\Facades\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.auth')] class extends Component {
    public string $email = '';

    /**
     * Send a password reset link to the provided email address.
     */
    public function sendPasswordResetLink(): void {
        $this->validate([
            'email' => [
                'required',
                'string',
                'email',
            ],
        ]);

        Password::sendResetLink($this->only('email'));
        session()->flash('status', __('auth.forgot_password.confirmation'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('auth.forgot_password.title')" :description="__('auth.forgot_password.description')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="sendPasswordResetLink" class="flex flex-col gap-6">
        <flux:input
            wire:model="email"
            :label="__('app.email')"
            type="email"
            required
            autofocus
            placeholder="email@example.com"
            viewable
        />

        <flux:button variant="primary" type="submit" class="w-full">{{ __('auth.forgot_password.submit') }}</flux:button>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-400">
        {{ __('auth.forgot_password.return_to') }}
        <flux:link :href="route('login')" wire:navigate>{{ mb_strtolower(__('app.login')) }}</flux:link>
    </div>
</div>
