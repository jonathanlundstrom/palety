<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\RecipientType;
use App\Helpers\ComponentHelpers;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {
    use ComponentHelpers;

    #[Locked]
    public bool $loading = false;

    #[Locked]
    public Recipient $recipient;

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
    }

    #[On('edit-recipient')]
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

    #[Validate('required')]
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
                    throw new Exception('notifications.recipients.failed');
                }
            } else {
                if (!Recipient::create($validated)) {
                    throw new Exception('notifications.recipients.failed');
                }
            }

            Flux::toast(variant: 'success', text: __('notifications.recipient.saved'));

            $this->dispatch('recipients-updated');
            $this->dispatch('modal-close', name: 'recipient-form');
            $this->reset(); // Reset the form properties
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    <flux:select variant="listbox" wire:model.live="parent_id" label="Parent recipient" placeholder="Choose parent recipient" clearable>
        @foreach ($this->recipients as $recipient)
            <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:select variant="listbox" wire:model.live="type" label="Type">
        @foreach (RecipientType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->name }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:input wire:model="name" label="Name"/>

    @if ($this->legalEntitySelected)
        <flux:input wire:model="organisation_number" label="EDRPOU code"/>
        <flux:input wire:model="reference" label="Reference person"/>
    @endif

    <flux:input type="email" icon="at-symbol" wire:model="email" label="E-mail address"/>
    <flux:input type="phone" icon="phone" wire:model="phone_number" label="Phone number"/>

    <flux:select variant="listbox" wire:model.live="delivery_type" label="Delivery type">
        @foreach (DeliveryType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->name }}</flux:select.option>
        @endforeach
    </flux:select>

    @if ($this->shouldBeDelivered)
        @if ($this->hasAddress)
            <flux:input wire:model="address" label="Address"/>
            <flux:input wire:model="zipcode" label="Zipcode"/>
        @else
            <flux:input type="number" min="1" max="1000" icon="hashtag" wire:model="nova_poshta_id" label="Nova Poshta ID"/>
        @endif

    @endif

    <flux:input wire:model="city" label="City"/>
    <flux:textarea wire:model="notes" label="Notes"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">Submit</flux:button>
    </div>
</form>
