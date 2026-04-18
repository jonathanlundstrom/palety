@php use App\Enumerables\DeliveryType; use App\Enumerables\PalletType; use App\Enumerables\RecipientType; @endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="bg-white">
        <flux:main class="p-8">
            <div class="declaration">
                <div class="relative mb-6 w-full">
                    <flux:heading size="xl" level="1">Packing list for transport #{{ $transport->id }}</flux:heading>
                    <flux:separator variant="subtle"/>
                </div>

                @foreach($loadedByRecipient as $goods)
                    @php
                        $recipient = $goods['model'];
                    @endphp
                    <div class="flex flex-row flex-nowrap mb-10 items-stretch">
                        <table>
                            <thead>
                            <tr>
                                <th scope="col" class="w-1/12">Type</th>
                                <th scope="col" class="w-1/12">ID</th>
                                <th scope="col" class="w-4/12">English label</th>
                                <th scope="col" class="w-4/12">Ukrainian label</th>
                                <th scope="col" class="w-2/12">Weight</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($goods['pallets'] as $pallet)
                                @if ($pallet->type === PalletType::MANUAL_PALLET)
                                    <tr>
                                        <td>Pallet</td>
                                        <td>{{ $pallet->id }}</td>
                                        <td>{{ $pallet->label_en }}</td>
                                        <td>{{ $pallet->label_ua }}</td>
                                        <td>{{ $pallet->getWeight() }} {{ __('app.weight.unit') }}</td>
                                    </tr>
                                @else
                                    @foreach ($pallet->parcels as $key => $parcel)
                                        <tr>
                                            @if ($key === 0)
                                                <td rowspan="{{ $pallet->parcels->count() }}">Pallet</td>
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
                                    <td>Parcel</td>
                                    <td>{{ $parcel->id }}</td>
                                    <td>{{ $parcel->contentList('en') }}</td>
                                    <td>{{ $parcel->contentList('ua') }}</td>
                                    <td>{{ $parcel->getWeight() }} {{ __('app.weight.unit') }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr>
                                <th colspan="4">Total weight:</th>
                                <td>{{ $goods['weight'] }} {{ __('app.weight.unit') }}</td>
                            </tr>
                            </tfoot>
                        </table>

                        <flux:card class="space-y-6 flex flex-2/12 items-center justify-center ml-6">
                            <div class="text-center">
                                <h2>{{ $recipient->name }}</h2>
                                @if ($recipient->type === RecipientType::ORGANISATION)
                                    {{ $recipient->organisation_number }}
                                @endif

                                @if ($parent = $recipient->parent)
                                    <h3>Subrecipient of {{ $parent->name }}</h3>
                                @endif
                                <br/>
                                {{ $recipient->delivery_type->label() }}

                                @if ($recipient->delivery_type === DeliveryType::ADDRESS_DELIVERY)
                                    {{ $recipient->address }}
                                    {{ $recipient->zipcode }}
                                    {{ $recipient->city }}
                                @elseif ($recipient->delivery_type === DeliveryType::NOVA_POSHTA_DELIVERY)
                                    Nova Poshta #{{ $recipient->nova_poshta_id }}, {{ $recipient->city }}
                                @endif

                                @if ($recipient->notes)
                                    <span>{{ $recipient->notes }}</span>
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
