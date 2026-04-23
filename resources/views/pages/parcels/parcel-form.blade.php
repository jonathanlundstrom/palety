<?php

use App\Enumerables\Availability;
use App\Enumerables\FormStatus;
use App\Enumerables\ParcelType;
use App\Events\ParcelSaved;
use App\Livewire\Components\FormComponent;
use App\Models\Content;
use App\Models\Parcel;
use App\Models\Recipient;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;

new class extends FormComponent {

    #[Validate('required')]
    public string $type = ParcelType::BOX->name;

    #[Validate('required')]
    public array $content;

    #[Validate('required')]
    public string $weight;

    #[Validate('nullable')]
    public string $notes;

    #[Validate('nullable|integer')]
    public int $recipient_id;

    #[Computed]
    protected function contentItems(): Collection {
        return Content::query()
            ->select('id', Content::label())
            ->orderBy(Content::label())
            ->get();
    }

    #[Computed]
    protected function recipients(): Collection {
        return Recipient::list(['id', 'name'], 'name')->get();
    }

    #[Computed]
    protected function canSelectRecipient(): bool {
        if ($this->formStatus() === FormStatus::EDITING) {
            return $this->resource->getAvailability() === Availability::AVAILABLE;
        } else {
            return true;
        }
    }

    /**
     * Create a new parcel from validated form data.
     * @param array $validated
     * @return Parcel
     */
    protected function createParcel(array $validated): Parcel {
        $parcel = Parcel::create($validated);
        if (!$parcel) throw new Exception('toasts.parcel.failed');
        return $parcel;
    }

    /**
     * Update an existing parcel with validated form data.
     * @param array $validated
     * @return Parcel
     */
    protected function updateParcel(array $validated): Parcel {
        $result = $this->resource->update($validated);
        if (!$result) throw new Exception('toasts.parcel.failed');
        return $this->resource;
    }

    /**
     * Handle the form submission event.
     * @return void
     */
    public function onSubmit(): void {
        $validated = [
            ...$this->validate(),
            'user_id' => Auth::id()
        ];

        try {
            $is_creating = $this->formStatus() === FormStatus::CREATING;
            $previous_weight = $is_creating ? null : $this->resource->weight;

            DB::transaction(function () use ($validated, $is_creating, $previous_weight) {
                $parcel = match ($this->formStatus()) {
                    FormStatus::EDITING => $this->updateParcel($validated),
                    FormStatus::CREATING => $this->createParcel($validated),
                };

                // Sync attached content after create/edit
                $parcel->content()->sync($this->content);

                // Fire event if created or on weight change:
                if ($is_creating || $parcel->weight != $previous_weight) {
                    event(new ParcelSaved($parcel));
                }
            });

            Flux::toast(variant: 'success', text: __('toasts.parcel.saved'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __($e->getMessage()));
        }
    }

}
?>
<form wire:submit="onSubmit" class="space-y-6 min-h-full">
    @if ($this->formStatus() === FormStatus::EDITING && $resource->getAvailability() === Availability::ALREADY_LOADED)
        <flux:callout variant="danger" heading="{!! __('app.parcel_loaded') !!}" icon="exclamation-circle" />
    @endif

    <flux:select variant="listbox" wire:model.live="type" label="{{ __('app.type') }}">
        @foreach (ParcelType::cases() as $case)
            <flux:select.option :value="$case->name">{{ $case->label() }}</flux:select.option>
        @endforeach
    </flux:select>

    <flux:pillbox variant="combobox" wire:model.live="content" label="{{ __('app.content.label') }}"
                  placeholder="{{ __('app.content.select') }}" multiple>
        @foreach ($this->contentItems as $item)
            <flux:select.option value="{{ $item->id }}">{{ $item->{Content::label()} }}</flux:select.option>
        @endforeach
    </flux:pillbox>

    <flux:field>
        <flux:label>{{ __('app.weight.label') }}</flux:label>
        <flux:input.group>
            <flux:input type="number" lang="en_EN" step="0.01" wire:model="weight"/>
            <flux:input.group.suffix>{{ __('app.weight.unit') }}</flux:input.group.suffix>
        </flux:input.group>
        <flux:error name="weight"/>
    </flux:field>

    @if ($this->canSelectRecipient)
        <flux:select variant="listbox" wire:model.live="recipient_id" label="{{ __('app.recipient') }}" clearable>
            @foreach ($this->recipients as $recipient)
                <flux:select.option value="{{ $recipient->id }}">{{ $recipient->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:callout variant="warning" icon="information-circle" heading="{{ __('pages.parcels.form.extras.recipient_warning') }}"/>
    @endif


    <flux:textarea wire:model="notes" label="{{ __('app.notes') }}"/>

    <div class="flex">
        <flux:spacer/>
        <flux:button type="submit" variant="primary">{{ __('app.submit') }}</flux:button>
    </div>
</form>
