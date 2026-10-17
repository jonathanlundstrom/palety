<?php

use App\Enumerables\FormStatus;
use App\Enumerables\ImportCategory;
use App\Enumerables\Permission;
use App\Enumerables\Role;
use App\Livewire\Components\FormComponent;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {
    /**
     * The model managed by this form. Edit and duplicate requests
     * for any other class are rejected by the FormComponent.
     */
    protected string $model = User::class;

    #[Validate('required')]
    public string $name;

    #[Validate('required|email')]
    public string $email;

    #[Validate(['roles' => ['required', 'array', 'min:1'], 'roles.*' => [new Enum(Role::class)]])]
    public array $roles = [Role::WAREHOUSE_VOLUNTEER->value];

    #[Validate('nullable|min:8')]
    public string $password = '';

    #[Validate('required')]
    public string $locale = 'en';

    #[Validate('required')]
    public string $timezone = 'Europe/Stockholm';

    /**
     * Skip the roles field, since it is hydrated from Grant below.
     * @return array<int, string>
     */
    protected function skipHydration(): array {
        return [...parent::skipHydration(), 'roles'];
    }

    /**
     * Hydrate the form fields, including the roles assigned through Grant.
     * @param object $object
     * @return void
     */
    protected function hydrateFields(object $object): void {
        parent::hydrateFields($object);

        $this->roles = $object->roles()
            ->map(fn (Role $role) => $role->value)
            ->all();
    }

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

        $user = DB::transaction(function () use ($validated) {
            return User::create($validated)
                ->syncRoles($this->selectedRoles());
        });

        if (!$user) throw new Exception('toasts.user.failed');
    }

    /**
     * Update existing user with validated form data.
     * @param array $validated
     */
    protected function updateUser(array $validated): void {
        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $result = DB::transaction(function () use ($validated) {
            $result = $this->resource->update($validated);
            if ($result && ! $this->isCurrentUser) {
                $this->resource->syncRoles($this->selectedRoles());
            }

            return $result;
        });

        if (!$result) throw new Exception('toasts.user.failed');
    }

    /**
     * Resolve the selected role values into Role instances.
     * @return array<int, Role>
     */
    protected function selectedRoles(): array {
        return array_map(Role::from(...), $this->roles);
    }

    /**
     * The roles the current user may assign. Only super admins may assign the super admin role.
     * @return array<int, Role>
     */
    #[Computed]
    public function assignableRoles(): array {
        return Auth::user()->hasRole(Role::SUPER_ADMIN)
            ? Role::cases()
            : array_values(array_filter(
                Role::cases(),
                fn (Role $role) => $role !== Role::SUPER_ADMIN
            ));
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $this->authorize(match ($this->formStatus()) {
            FormStatus::CREATING => Permission::CREATE_USER,
            FormStatus::EDITING => Permission::EDIT_USER,
        });

        // Prevent non-super admin users from editing super admins.
        abort_if($this->resource?->hasRole(Role::SUPER_ADMIN) && !Auth::user()->hasRole(Role::SUPER_ADMIN), 403);

        // Append validation rules that are conditional.
        $this->withValidator(function ($validator) {
            $validator->addRules([
                'email' => $this->formStatus() === FormStatus::EDITING
                    ? Rule::unique('users')->ignore($this->resource->id)
                    : Rule::unique('users'),
                'password' => $this->formStatus() === FormStatus::CREATING
                    ? 'required'
                    : 'nullable',
                'roles.*' => Rule::enum(Role::class)->only($this->assignableRoles),
            ]);
        });

        $validated = $this->validate();

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

    /**
     * Validate if the currently logged-in user is the user being edited.
     * @return bool
     */
    #[Computed]
    public function isCurrentUser(): bool {
        return Auth::user()->id === $this->resource?->id;
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <x-form-heading :title="__('pages.users.form.'.$this->formMode.'.title')"
                    :subtitle="__('pages.users.form.'.$this->formMode.'.subtitle')"/>

    <flux:input wire:model="name" label="{{ __('app.name') }}"/>
    <flux:input wire:model="email" label="{{ __('app.email') }}"/>

    <flux:input type="password" wire:model="password" label="{{ __('app.password') }}" viewable/>

    @if ($this->formStatus() === FormStatus::EDITING)
        <flux:callout variant="warning" icon="information-circle"
                      heading="{{ __('pages.users.form.extras.password_warning') }}"/>
    @endif

    <flux:pillbox wire:model="roles" multiple label="{{ trans_choice('app.role.label', 2) }}"
                  placeholder="{{ __('app.role.select') }}" :disabled="$this->isCurrentUser">
        @foreach ($this->assignableRoles as $case)
            <flux:pillbox.option :value="$case->value">{{ $case->label() }}</flux:pillbox.option>
        @endforeach
    </flux:pillbox>

    <flux:select variant="listbox" wire:model.live="locale" label="{{ __('app.locale.label') }}">
        <flux:select.option value="sv">{{ __('app.locales.sv') }}</flux:select.option>
        <flux:select.option value="en">{{ __('app.locales.en') }}</flux:select.option>
        <flux:select.option value="uk">{{ __('app.locales.uk') }}</flux:select.option>
    </flux:select>

    <flux:select variant="listbox" wire:model.live="timezone" label="{{ __('app.timezone.label') }}" searchable>
        @foreach (DateTimeZone::listIdentifiers(DateTimeZone::ALL) as $timezone)
            <flux:select.option value="{{ $timezone }}">{{ $timezone }}</flux:select.option>
        @endforeach
    </flux:select>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ $this->formActionLabel }}</flux:button>
    </div>
</form>
