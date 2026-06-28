<?php

use App\Models\Content;
use Flux\Flux;
use hisorange\BrowserDetect\Facade as Browser;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;

new class extends Component {

    #[Locked]
    public ?int $source_id = null;

    #[Validate('required|integer')]
    public ?int $target_id = null;

    #[On('confirm-merge')]
    public function prepare(int $id): void {
        $this->source_id = $id;
        $this->target_id = null;
        $this->resetValidation();
    }

    #[On('reset-modal')]
    public function clear(): void {
        $this->reset();
        $this->resetValidation();
    }

    #[Computed]
    public function source(): ?Content {
        return $this->source_id
            ? Content::withCount(['parcels', 'pallets'])->find($this->source_id)
            : null;
    }

    #[Computed]
    public function candidates(): Collection {
        return Content::orderBy(Content::label())
            ->when($this->source_id, fn($q) => $q->where('id', '!=', $this->source_id))
            ->get();
    }

    #[Computed]
    public function sourceHeading(): ?string {
        if (! $this->source) {
            return null;
        }

        return __('modals.merge.source_heading', [
            'label' => $this->source->{Content::label()},
            'count' => $this->source->usage_count,
            'unit'  => trans_choice('app.usage.unit', $this->source->usage_count),
        ]);
    }

    #[Computed]
    public function isFlyout(): bool {
        return !Browser::isDesktop();
    }

    /**
     * Handle the merge of selected content.
     *
     * @return void
     */
    public function onSubmit(): void {
        $this->validate();

        try {
            DB::transaction(function () {
                $lookup = [
                    'content_parcel' => 'parcel_id',
                    'content_pallet' => 'pallet_id'
                ];

                foreach ($lookup as $table => $fk) {
                    DB::table($table)
                        ->where('content_id', $this->source_id)
                        ->whereIn($fk, DB::table($table)->where('content_id', $this->target_id)->pluck($fk))
                        ->delete();

                    DB::table($table)
                        ->where('content_id', $this->source_id)
                        ->update(['content_id' => $this->target_id]);
                }

                Content::findOrFail($this->source_id)
                    ->forceDelete(); // Bypass the soft delete. Not used for now.
            });

            Flux::toast(variant: 'success', text: __('toasts.content.merge.success'));
            $this->dispatch('items-updated');
            $this->dispatch('modal-close');
        } catch (Exception $e) {
            Flux::toast(variant: 'danger', text: __('toasts.content.merge.failed'));
        }
    }

}

?>
<flux:modal name="merge-content" class="w-xs sm:w-10/12 md:w-128" :flyout="$this->isFlyout" position="bottom"
            x-on:close="$wire.dispatch('reset-modal')">
    <div class="space-y-6">
        <div>
            <flux:heading size="lg">{{ __('modals.merge.title') }}</flux:heading>
            <flux:text class="mt-2">{{ __('modals.merge.subtitle') }}</flux:text>
        </div>

        <form wire:submit="onSubmit" class="space-y-6">
            @if ($this->source)
                <flux:callout variant="warning" icon="information-circle" :heading="$this->sourceHeading"/>
            @else
                <flux:skeleton animate="shimmer" class="size-full rounded-lg" style="height: 3.375rem"/>
            @endif

            <flux:select variant="listbox" wire:model.live="target_id" :label="__('modals.merge.target_label')" searchable clearable>
                @foreach ($this->candidates as $candidate)
                    <flux:select.option :value="$candidate->id" wire:key="{{ $candidate->id }}">
                        {{ $candidate->{Content::label()} }}
                    </flux:select.option>
                @endforeach
            </flux:select>

            <div class="flex justify-between">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('app.cancel') }}</flux:button>
                </flux:modal.close>

                <flux:button type="submit" variant="danger" :disabled="!$target_id">
                    {{ __('modals.merge.confirm') }}
                </flux:button>
            </div>
        </form>
    </div>
</flux:modal>
