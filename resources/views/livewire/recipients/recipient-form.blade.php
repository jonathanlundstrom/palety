<?php

use App\Enumerables\DeliveryType;
use App\Enumerables\FormVariant;
use App\Enumerables\RecipientType;
use App\Models\Recipient;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {

    /**
     * The recipient to edit, if any.
     * @var Recipient|null
     */
    public ?Recipient $recipient = null;

    #[Validate('integer')]
    public int $parent_id;

    #[Validate('required')]
    public string $type = RecipientType::ORGANISATION->name;

    #[Validate('required')]
    public string $name;

    #[Validate('required_if:type,' . RecipientType::ORGANISATION->name)]
    public string $organisation_number;

    #[Validate('required_if:type,' . RecipientType::ORGANISATION->name)]
    public string $reference;

    #[Validate('email')]
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
    protected function isEditForm(): bool {
        return !is_null($this->recipient);
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

    public function create() {
        $this->validate();
        dump('CREATE!');
        $this->dispatch('recipient-created', recipient: []);
    }

    public function update() {
        $this->validate();
        dump('UPDATE!');
        $this->dispatch('recipient-updated', recipient: []);
    }

}
?>
<form wire:submit="save" class="space-y-6">
    {{ $this->isEditForm ? 'EDIT' : 'CREATE' }}

    <flux:select variant="listbox" wire:model.live="parent_id" label="Parent recipient">
        <!-- Will be populated soon... -->
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
            <flux:input type="number" min="1" max="1000" icon="hashtag" wire:model="nova_poshta_id"
                        label="Nova Poshta ID"/>
        @endif

        <flux:input wire:model="city" label="City"/>
    @endif

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">Add recipient</flux:button>
    </div>
</form>
