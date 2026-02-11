<?php

use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Component;

/**
 * This component is based on Laravel boilerplate and
 * will only get minor cleanup to be somewhat compatible
 * with the structure of the rest of the project.
 */
new class extends Component {
    public string $name = '';
    public string $email = '';
    public string $locale = '';
    public string $timezone = '';

    /**
     * Mount the component.
     */
    public function mount(): void {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->locale = Auth::user()->locale;
        $this->timezone = Auth::user()->timezone;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],
            'locale' => ['required', 'string'],
            'timezone' => ['required', 'string'],
        ]);

        $user->fill($validated);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($user->save()) {
            Flux::toast(variant: 'success', text: __('toasts.user.saved'));
        } else {
            Flux::toast(variant: 'danger', text: __('toasts.user.failed'));
        }
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void {
        $user = Auth::user();
        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));
            return;
        }

        $user->sendEmailVerificationNotification();
        Session::flash('status', 'verification-link-sent');
    }
}

?>
<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('pages.settings.subpages.profile.headline')" :subheading="__('pages.settings.subpages.profile.subtitle')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('app.name')" type="text" required autofocus autocomplete="name"/>

            <div>
                <flux:input wire:model="email" :label="__('app.email')" type="email" required autocomplete="email"/>
                @if (auth()->user() instanceof MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('pages.settings.subpages.profile.extras.email_unverified') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('pages.settings.subpages.profile.extras.resend_verification') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('pages.settings.subpages.profile.extras.verification_sent') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <flux:select variant="listbox" wire:model.live="locale" label="{{ __('app.locale.label') }}">
                <flux:select.option value="en">{{ __('app.locales.en') }}</flux:select.option>
                <flux:select.option value="ua">{{ __('app.locales.ua') }}</flux:select.option>
            </flux:select>

            <flux:select variant="listbox" wire:model.live="timezone" label="{{ __('app.timezone.label') }}" searchable>
                @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $timezone)
                    <flux:select.option value="{{ $timezone }}">{{ $timezone }}</flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('app.save') }}</flux:button>
                </div>
            </div>
        </form>

        @if (Auth::user()->role === 'admin')
            <livewire:pages::settings.delete-user-form/>
        @endif
    </x-settings.layout>
</section>
