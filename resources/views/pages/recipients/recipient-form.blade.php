<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\FormStatus;
use App\Enumerables\RecipientType;
use App\Livewire\Components\FormComponent;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[Validate('nullable|integer')]
    public ?int $parent_id = null;

    #[Validate('required')]
    public string $type = RecipientType::ORGANISATION->name;

    #[Validate('required')]
    public string $name;

    #[Validate('required_if:type,' . RecipientType::ORGANISATION->name)]
    public string $organisation_number;

    #[Validate('required_if:type,' . RecipientType::ORGANISATION->name)]
    public string $reference;

    #[Validate('nullable|email')]
    public string $email;

    #[Validate('required|phone')]
    public string $phone_number;

    #[Validate('required')]
    public string $delivery_type = DeliveryType::NOVA_POSHTA_DELIVERY->name;

    #[Validate('required_if:delivery_type,' . DeliveryType::ADDRESS_DELIVERY->name)]
    public string $address;

    #[Validate('required_if:delivery_type,' . DeliveryType::ADDRESS_DELIVERY->name)]
    public string $zipcode;

    #[Validate('required')]
    public string $city;

    #[Validate('required_if:delivery_type,' . DeliveryType::NOVA_POSHTA_DELIVERY->name)]
    public string $nova_poshta_id;

    #[Validate('nullable')]
    public string $notes;

    #[Computed]
    protected function recipients(): Collection {
        return Recipient::list(['id', 'name'], 'name')->get();
    }

    #[Computed]
    protected function legalEntitySelected(): bool {
        return RecipientType::from($this->type)->isLegalEntity();
    }

    #[Computed]
    protected function shouldBeDelivered(): bool {
        return DeliveryType::from($this->delivery_type)->isDelivery();
    }

    #[Computed]
    protected function hasAddress(): bool {
        return DeliveryType::from($this->delivery_type)->hasAddress();
    }

    /**
     * Create a new recipient from validated form data.
     * @param array $validated
     * @return void
     */
    protected function createRecipient(array $validated): void {
        $recipient = Recipient::create($validated);
        if (!$recipient) throw new Exception('toasts.recipient.failed');
    }

    /**
     * Update an existing recipient with validated form data.
     * @param array $validated
     * @return void
     */
    protected function updateRecipient(array $validated): void {
        $result = $this->resource->update($validated);
        if (!$result) throw new Exception('toasts.recipient.failed');
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $validated = $this->validate();

        try {
            match($this->formStatus()) {
                FormStatus::EDITING => $this->updateRecipient($validated),
                FormStatus::CREATING => $this->createRecipient($validated),
            };

            Flux::toast(variant: 'success', text: __('toasts.recipient.saved'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }
}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:select variant="listbox" wire:model.live="parent_id" label="{{ __('app.parent.label') }}"
                 placeholder="{{ __('app.parent.select') }}" clearable>
        @foreach ($this->recipients as $recipient)
            <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="type" label="{{ __('app.type') }}">
        @foreach (RecipientType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:input wire:model="name" label="{{ __('app.name') }}"/>

    @if ($this->legalEntitySelected)
        <flux:input wire:model="organisation_number"
                    label="{{ __('app.organisation_number') }} ({{ __('pages.recipients.form.extras.EDRPOU') }})"/>
        <flux:input icon="user" wire:model="reference" label="{{ __('app.reference') }}"/>
    @endif

    <flux:input type="email" icon="at-symbol" wire:model="email" label="{{ __('app.email') }}"/>
    <flux:input type="phone" icon="phone" wire:model="phone_number"
                label="{{ __('app.phone_number') }}"/>

    <flux:select variant="listbox" wire:model.live="delivery_type"
                 label="{{ __('app.delivery_type') }}">
        @foreach (DeliveryType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->shouldBeDelivered)
        @if ($this->hasAddress)
            <flux:input wire:model="address" label="{{ __('app.address') }}"/>
            <flux:input wire:model="zipcode" label="{{ __('app.zipcode') }}"/>
        @else
            <flux:input type="number" min="1" max="1000" icon="hashtag" wire:model="nova_poshta_id"
                        label="{{ __('app.nova_poshta_id') }}"/>
        @endif
    @endif

    <flux:input wire:model="city" label="{{ __('app.city') }}"/>
    <flux:textarea wire:model="notes" label="{{ __('app.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
