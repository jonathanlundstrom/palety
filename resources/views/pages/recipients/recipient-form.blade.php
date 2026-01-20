<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use App\Livewire\Components\FormComponent;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[Locked]
    public Recipient $recipient;

    #[On('edit-resource')]
    public function edit(int $id): void {
        $this->recipient = Recipient::find($id);
        $this->hydrateFields($this->recipient);
    }

    #[Validate('integer|nullable')]
    public ?int $parent_id = null;

    #[Validate('required')]
    public string $type = RecipientType::ORGANISATION->name;

    #[Validate('required')]
    public string $name;

    #[Validate('required_if:type,' . RecipientType::ORGANISATION->name)]
    public string $organisation_number;

    #[Validate('required_if:type,' . RecipientType::ORGANISATION->name)]
    public string $reference;

    #[Validate('email|nullable')]
    public string $email;

    #[Validate('required|phone')]
    public string $phone_number;

    #[Validate('required')]
    public string $delivery_type = DeliveryType::NOVA_POSHTA_DELIVERY->name;

    #[Validate('required_if:delivery_type,' . DeliveryType::ADDRESS_DELIVERY->name)]
    public string $address;

    #[Validate('required_if:delivery_type,' . DeliveryType::ADDRESS_DELIVERY->name)]
    public string $zipcode;

    #[Validate('required_unless:delivery_type,' . DeliveryType::SELF_PICKUP->name)]
    public string $city;

    #[Validate('required_if:delivery_type,' . DeliveryType::NOVA_POSHTA_DELIVERY->name)]
    public string $nova_poshta_id;

    #[Computed]
    protected function recipients(): Collection {
        return Recipient::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
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

    public function onSubmit() {
        $validated = $this->validate();

        try {
            if (isset($this->recipient) && $this->recipient->exists) {
                if (!$this->recipient->update($validated)) {
                    throw new Exception('toasts.recipients.failed');
                }
            } else {
                if (!Recipient::create($validated)) {
                    throw new Exception('toasts.recipients.failed');
                }
            }

            Flux::toast(variant: 'success', text: __('toasts.recipients.saved'));

            $this->dispatch('recipients-updated');
            $this->dispatch('modal-close', name: 'recipient-form');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:select variant="listbox" wire:model.live="parent_id" label="{{ __('validation.attributes.parent_id') }}"
                 placeholder="{{ __('app.parent.select') }}" clearable>
        @foreach ($this->recipients as $recipient)
            <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="type" label="{{ __('validation.attributes.type') }}">
        @foreach (RecipientType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:input wire:model="name" label="{{ __('validation.attributes.name') }}"/>

    @if ($this->legalEntitySelected)
        <flux:input wire:model="organisation_number"
                    label="{{ __('validation.attributes.organisation_number') }} – {{ __('pages.recipients.form.extras.EDRPOU') }}"/>
        <flux:input icon="user" wire:model="reference" label="{{ __('validation.attributes.reference') }}"/>
    @endif

    <flux:input type="email" icon="at-symbol" wire:model="email" label="{{ __('validation.attributes.email') }}"/>
    <flux:input type="phone" icon="phone" wire:model="phone_number"
                label="{{ __('validation.attributes.phone_number') }}"/>

    <flux:select variant="listbox" wire:model.live="delivery_type"
                 label="{{ __('validation.attributes.delivery_type') }}">
        @foreach (DeliveryType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->shouldBeDelivered)
        @if ($this->hasAddress)
            <flux:input wire:model="address" label="{{ __('validation.attributes.address') }}"/>
            <flux:input wire:model="zipcode" label="{{ __('validation.attributes.zipcode') }}"/>
        @else
            <flux:input type="number" min="1" max="1000" icon="hashtag" wire:model="nova_poshta_id"
                        label="{{ __('validation.attributes.nova_poshta_id') }}"/>
        @endif

    @endif

    <flux:input wire:model="city" label="{{ __('validation.attributes.city') }}"/>
    <flux:textarea wire:model="notes" label="{{ __('validation.attributes.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
