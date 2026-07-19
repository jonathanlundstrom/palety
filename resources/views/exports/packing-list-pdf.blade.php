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
                            <flux:heading size="xl" level="1" class="font-semibold mb-1">{{ __('app.packing_list.for_transport', ['id' => $transport->id]) }}</flux:heading>
                            <div class="flex flew-row justify-start gap-2">
                                @if ($palletCount = $transport->pallets->count())
                                    <flux:text class="text-lg">{{ $palletCount }} {{ mb_strtolower(trans_choice('app.pallet', $palletCount)) }}</flux:text>
                                    <flux:text class="text-lg">&bull;</flux:text>
                                @endif

                                @if ($parcelCount = $transport->parcels->count())
                                    <flux:text class="text-lg">{{ $parcelCount }} {{ mb_strtolower(trans_choice('app.parcel', $parcelCount)) }}</flux:text>
                                    <flux:text class="text-lg">&bull;</flux:text>
                                @endif

                                @if ($transport->notes)
                                    <flux:text class="text-lg">{{ $transport->notes }}</flux:text>
                                @endif
                            </div>
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
                        <table class="w-10/12" style="background-color: {{ $recipient->color }}80">
                            <tbody>
                                @if ($recipient->color)
                                    <tr class="font-bold" style="background-color: {{ $recipient->color }}">
                                @else
                                    <tr class="font-bold !bg-gray-100">
                                @endif
                                    <td class="w-1/12">{{ __('app.type') }}</td>
                                    <td class="w-1/12">{{ __('app.id') }}</td>
                                    <td class="w-3/12">{{ __('app.label_en') }}</td>
                                    <td class="w-3/12">{{ __('app.label_ua') }}</td>
                                    <td class="w-2/12">{{ __('app.notes') }}</td>
                                    <td class="w-2/12">{{ __('app.weight.label') }}</td>
                                </tr>

                                @foreach($goods['pallets'] as $pallet)
                                    @if ($pallet->type === PalletType::MANUAL_PALLET)
                                        <tr>
                                            <td>{{ trans_choice('app.pallet', 1) }}</td>
                                            <td>{{ $pallet->id }}</td>
                                            <td>{{ $pallet->contentList('en') }}</td>
                                            <td>{{ $pallet->contentList('ua') }}</td>
                                            <td>{{ $pallet->notes }}</td>
                                            <td>{{ $pallet->getWeight() }} {{ __('app.weight.unit') }}</td>
                                        </tr>
                                    @else
                                        @foreach ($pallet->parcels as $key => $parcel)
                                            <tr>
                                                @if ($key === 0)
                                                    <td rowspan="{{ $pallet->parcels->count() }}" class="align-middle">{{ trans_choice('app.pallet', 1) }}</td>
                                                @endif
                                                <td class="align-text-top">{{ $parcel->id }} <span class="opacity-30">({{$pallet->id}})</span></td>
                                                <td>{{ $parcel->contentList('en') }}</td>
                                                <td>{{ $parcel->contentList('ua') }}</td>
                                                <td>{{ $parcel->notes }}</td>
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
                                        <td>{{ $parcel->notes }}</td>
                                        <td>{{ $parcel->getWeight() }} {{ __('app.weight.unit') }}</td>
                                    </tr>
                                @endforeach

                                <tr class="font-bold" style="background-color: {{ $recipient->color }}">
                                    <th colspan="5">{{ __('app.total_weight') }}:</th>
                                    <td>
                                        <strong>{{ $goods['weight'] }} {{ __('app.weight.unit') }}</strong>
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="w-2/12 ml-6 flex flex-col">
                            @if ($recipient->color)
                                <div class="w-full h-4" style="background-color: {{ $recipient->color }}"></div>
                            @endif

                            <flux:card class="flex-auto flex flex-col p-6 text-center items-center justify-start rounded-none gap-3">
                                <div>
                                    <flux:heading level="3" class="font-semibold mb-1">{{ $recipient->name }}</flux:heading>
                                    @if ($recipient->type === RecipientType::ORGANISATION)
                                        <flux:text>{{ __('pages.recipients.form.extras.EDRPOU') }}: {{ $recipient->organisation_number }}</flux:text>
                                    @else
                                        <flux:text>{{ __('pages.recipients.form.extras.IPN') }}: {{ $recipient->tax_id }}</flux:text>
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

                                <div>
                                    @if ($palletCount = $goods['pallets']->count())
                                        <flux:text>{{ $palletCount }} {{ mb_strtolower(trans_choice('app.pallet', $palletCount)) }}</flux:text>
                                    @endif

                                    @if ($parcelCount = $goods['parcels']->count())
                                        <flux:text>{{ $parcelCount }} {{ mb_strtolower(trans_choice('app.parcel', $parcelCount)) }}</flux:text>
                                    @endif
                                </div>

                                @if ($recipient->notes)
                                    <div>
                                        <flux:text variant="subtle">{{ $recipient->notes }}</flux:text>
                                    </div>
                                @endif
                            </flux:card>
                        </div>
                    </div>
                @endforeach
            </div>
        </flux:main>

        @fluxScripts
    </body>
</html>
