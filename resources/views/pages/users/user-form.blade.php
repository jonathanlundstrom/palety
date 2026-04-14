<?php

use App\Enumerables\FormStatus;
use App\Enumerables\ImportCategory;
use App\Enumerables\UserRole;
use App\Livewire\Components\FormComponent;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {
    #[Validate('required')]
    public string $name;

    #[Validate('required|email')]
    public string $email;

    #[Validate('required')]
    public string $role = UserRole::USER->name;

    #[Validate('nullable|min:8')]
    public string $password;

    #[Validate('required')]
    public string $locale = 'en';

    #[Validate('required')]
    public string $timezone = 'Europe/Stockholm';

    /**
     * Create a new user from validated form data.
     * @param array $validated
     * @return void
     */
    protected function createUser(array $validated): void {
        $existing = User::query()
            ->where('name', $validated['name'])
            ->orWhere('email', $validated['email'])
            ->first();

        if ($existing) {
            throw new Exception('toasts.user.duplicate');
        }

        $content = User::create($validated);
        if (!$content) throw new Exception('toasts.user.failed');
    }

    /**
     * Update existing user with validated form data.
     * @param array $validated
     */
    protected function updateUser(array $validated): void {
        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $result = $this->resource->update($validated);
        if (!$result) throw new Exception('toasts.user.failed');
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $validated = $this->validate();

        // Extra validation for e-mail uniqueness:
        $this->validateOnly('email', [
            'email' => ['required', 'email', Rule::unique('users')->ignore($this->resource?->id)],
        ]);

        try {
            match ($this->formStatus()) {
                FormStatus::EDITING => $this->updateUser($validated),
                FormStatus::CREATING => $this->createUser($validated),
            };

            Flux::toast(variant: 'success', text: __('toasts.user.saved'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:input wire:model="name" label="{{ __('app.name') }}"/>
    <flux:input wire:model="email" label="{{ __('app.email') }}"/>

    <flux:select variant="listbox" wire:model.live="role" label="{{ trans_choice('app.role.label', 1) }}"
                 placeholder="{{ __('app.role.select') }}">
        @foreach (UserRole::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:input type="password" wire:model="password" label="{{ __('app.password') }}" viewable/>

    @if ($this->formStatus() === FormStatus::EDITING)
        <flux:callout variant="warning" icon="information-circle"
                      heading="Only enter a password in the field if you want to change the current one!"/>
    @endif

    <flux:select variant="listbox" wire:model.live="locale" label="{{ __('app.locale.label') }}">
        <flux:select.option value="en">{{ __('app.locales.en') }}</flux:select.option>
        <flux:select.option value="ua">{{ __('app.locales.ua') }}</flux:select.option>
    </flux:select>

    <flux:select variant="listbox" wire:model.live="timezone" label="{{ __('app.timezone.label') }}" searchable>
        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $timezone)
            <flux:select.option value="{{ $timezone }}">{{ $timezone }}</flux:select.option>
        @endforeach
    </flux:select>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
