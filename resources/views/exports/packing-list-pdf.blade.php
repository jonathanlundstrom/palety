@php
    use App\Enumerables\DeliveryType;
    use App\Enumerables\PalletType;
    use App\Enumerables\RecipientType;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white">
        <flux:main class="p-8">
            <div class="declaration">
                <header class="relative mb-8 w-full">
                    <div class="flex flew-row justify-between items-center">
                        <div>
                            <flux:heading size="xl" level="1" class="font-semibold mb-1">{{ __('app.packing_list_for_transport', ['id' => $transport->id]) }}</flux:heading>
                            @if ($transport->notes)
                                <flux:text class="text-lg mb-1">{{ $transport->notes }}</flux:text>
                            @endif
                        </div>

                        <div class="flex-0 flex justify-center gap-2">
                            <flux:text class="text-lg font-semibold whitespace-nowrap flex">
                                <flux:icon.scale class="size-8 mr-2"/>
                                {{ $transport->getWeight() }} {{ __('app.weight.unit') }}
                            </flux:text>
                        </div>
                    </div>

                    <flux:separator variant="subtle" class="mt-6" />
                </header>

                @foreach($loadedByRecipient as $goods)
                    @php $recipient = $goods['model']; @endphp
                    <div class="flex flex-row flex-nowrap mb-10 items-stretch">
                        <table>
                            <thead class="!bg-zinc-50">
                                <tr>
                                    <th scope="col" class="w-1/12">{{ __('app.type') }}</th>
                                    <th scope="col" class="w-1/12">{{ __('app.id') }}</th>
                                    <th scope="col" class="w-4/12">{{ __('app.label_en') }}</th>
                                    <th scope="col" class="w-4/12">{{ __('app.label_ua') }}</th>
                                    <th scope="col" class="w-2/12">{{ __('app.weight.label') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($goods['pallets'] as $pallet)
                                    @if ($pallet->type === PalletType::MANUAL_PALLET)
                                        <tr>
                                            <td>{{ trans_choice('app.pallet', 1) }}</td>
                                            <td>{{ $pallet->id }}</td>
                                            <td>{{ $pallet->contentList('en') }}</td>
                                            <td>{{ $pallet->contentList('ua') }}</td>
                                            <td>{{ $pallet->getWeight() }} {{ __('app.weight.unit') }}</td>
                                        </tr>
                                    @else
                                        @foreach ($pallet->parcels as $key => $parcel)
                                            <tr>
                                                @if ($key === 0)
                                                    <td rowspan="{{ $pallet->parcels->count() }}">{{ trans_choice('app.pallet', 1) }}</td>
                                                    <td rowspan="{{ $pallet->parcels->count() }}">{{ $pallet->id }}</td>
                                                @endif
                                                <td>{{ $parcel->contentList('en') }}</td>
                                                <td>{{ $parcel->contentList('ua') }}</td>
                                                <td>{{ $parcel->getWeight() }} {{ __('app.weight.unit') }}</td>
                                            </tr>
                                        @endforeach
                                    @endif
                                @endforeach

                                @foreach($goods['parcels'] as $parcel)
                                    <tr>
                                        <td>{{ trans_choice('app.parcel', 1) }}</td>
                                        <td>{{ $parcel->id }}</td>
                                        <td>{{ $parcel->contentList('en') }}</td>
                                        <td>{{ $parcel->contentList('ua') }}</td>
                                        <td>{{ $parcel->getWeight() }} {{ __('app.weight.unit') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="!bg-white">
                                <tr>
                                    <th colspan="4">{{ __('app.total_weight') }}:</th>
                                    <td>
                                        <strong>{{ $goods['weight'] }} {{ __('app.weight.unit') }}</strong>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>

                        <flux:card class="space-y-6 flex flex-2/12 items-center justify-center ml-6 rounded-none">
                            <div class="text-center">
                                <div class="mb-4">
                                    <flux:heading level="3" class="font-semibold mb-1">{{ $recipient->name }}</flux:heading>
                                    @if ($recipient->type === RecipientType::ORGANISATION)
                                        <flux:text>{{ __('pages.recipients.form.extras.EDRPOU') }}: {{ $recipient->organisation_number }}</flux:text>
                                    @endif

                                    @if ($parent = $recipient->parent)
                                        <flux:text>{{ __('app.subrecipient_of', ['name' => $parent->name]) }}</flux:text>
                                    @endif
                                </div>

                                <div>
                                    @if ($recipient->delivery_type === DeliveryType::SELF_PICKUP)
                                        <flux:text>{{ DeliveryType::SELF_PICKUP->label() }} in {{ $recipient->city }}</flux:text>
                                    @elseif ($recipient->delivery_type === DeliveryType::ADDRESS_DELIVERY)
                                        <flux:text>{{ $recipient->address }}, {{ $recipient->zipcode }} {{ $recipient->city }}</flux:text>
                                    @elseif ($recipient->delivery_type === DeliveryType::NOVA_POSHTA_DELIVERY)
                                        <flux:text>{{ __('app.nova_poshta') }} #{{ $recipient->nova_poshta_id }}, {{ $recipient->city }}</flux:text>
                                    @endif
                                </div>

                                @if ($recipient->notes)
                                    <div class="mt-4">
                                        <flux:text variant="subtle">{{ $recipient->notes }}</flux:text>
                                    </div>
                                @endif
                            </div>
                        </flux:card>
                    </div>
                @endforeach
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>
