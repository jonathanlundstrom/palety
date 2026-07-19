@php
    use App\Enumerables\DeliveryType;
    use App\Enumerables\PalletType;
    use App\Enumerables\RecipientType;
@endphp
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <table>
        <tbody>
            <tr>
                <td colspan="6">{{ __('app.packing_list.for_transport', ['id' => $transport->id]) }}</td>
                <td>{{ $transport->getWeight() }} {{ __('app.weight.unit') }}</td>
            </tr>
            <tr>
                <td colspan="1">{{ $transport->pallets->count() }} {{ mb_strtolower(trans_choice('app.pallet', $transport->pallets->count())) }}</td>
                <td colspan="1">{{ $transport->parcels->count() }} {{ mb_strtolower(trans_choice('app.parcel', $transport->parcels->count())) }}</td>
                @if ($transport->notes)
                <td colspan="5">{{ $transport->notes }}</td>
                 @endif
            </tr>
            <tr><td colspan="7"></td></tr>
            <tr><td colspan="7"></td></tr>

            @foreach($loadedByRecipient as $goods)
                @php
                    $rowCount = 0;
                    $isFirstRow = true;
                    $recipientLines = [];
                    $recipient = $goods['model'];

                    foreach ($goods['pallets'] as $pallet) {
                        $rowCount += $pallet->type === PalletType::MANUAL_PALLET
                            ? 1
                            : $pallet->parcels->count();
                    }

                    $rowCount += $goods['parcels']->count();
                    $rowCount += 1; // total weight row

                    $recipientLines[] = $recipient->name;

                    if ($recipient->type === RecipientType::ORGANISATION) {
                        $recipientLines[] = __('pages.recipients.form.extras.EDRPOU') . ': ' . $recipient->organisation_number;
                    }

                    if ($parent = $recipient->parent) {
                        $recipientLines[] = __('app.subrecipient_of', ['name' => $parent->name]);
                    }

                    if ($recipient->delivery_type === DeliveryType::SELF_PICKUP) {
                        $recipientLines[] = DeliveryType::SELF_PICKUP->label() . ' in ' . $recipient->city;
                    } elseif ($recipient->delivery_type === DeliveryType::ADDRESS_DELIVERY) {
                        $recipientLines[] = $recipient->address . ', ' . $recipient->zipcode . ' ' . $recipient->city;
                    } elseif ($recipient->delivery_type === DeliveryType::NOVA_POSHTA_DELIVERY) {
                        $recipientLines[] = __('app.nova_poshta') . ' #' . $recipient->nova_poshta_id . ', ' . $recipient->city;
                    }

                    if ($palletCount = $goods['pallets']->count()) {
                        $recipientLines[] = $palletCount . ' ' . mb_strtolower(trans_choice('app.pallet', $palletCount));
                    }

                    if ($parcelCount = $goods['parcels']->count()) {
                        $recipientLines[] = $parcelCount . ' ' . mb_strtolower(trans_choice('app.parcel', $parcelCount));
                    }

                    if ($recipient->notes) {
                        $recipientLines[] = $recipient->notes;
                    }

                    $recipientCell = implode("<br>", $recipientLines);
                @endphp

                @foreach($goods['pallets'] as $pallet)
                    @if ($pallet->type === PalletType::MANUAL_PALLET)
                        <tr>
                            <td>{{ trans_choice('app.pallet', 1) }}</td>
                            <td>{{ $pallet->id }}</td>
                            <td>{{ $pallet->contentList('en') }}</td>
                            <td>{{ $pallet->contentList('ua') }}</td>
                            <td>{{ $pallet->notes }}</td>
                            <td>{{ $pallet->getWeight() }}</td>
                            @if ($isFirstRow)
                                @php $isFirstRow = false; @endphp
                                <td rowspan="{{ $rowCount }}">{!! $recipientCell !!}</td>
                            @endif
                        </tr>
                    @else
                        @foreach ($pallet->parcels as $key => $parcel)
                            <tr>
                                @if ($key === 0)
                                    <td rowspan="{{ $pallet->parcels->count() }}">{{ trans_choice('app.pallet', 1) }}</td>
                                @endif
                                <td>{{ $parcel->id }} ({{$pallet->id}})</td>
                                <td>{{ $parcel->contentList('en') }}</td>
                                <td>{{ $parcel->contentList('ua') }}</td>
                                <td>{{ $parcel->notes }}</td>
                                <td>{{ $parcel->getWeight() }}</td>
                                @if ($isFirstRow)
                                    @php $isFirstRow = false; @endphp
                                    <td rowspan="{{ $rowCount }}">{!! $recipientCell !!}</td>
                                @endif
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
                        <td>{{ $parcel->getWeight() }}</td>
                        @if ($isFirstRow)
                            @php $isFirstRow = false; @endphp
                            <td rowspan="{{ $rowCount }}">{!! $recipientCell !!}</td>
                        @endif
                    </tr>
                @endforeach
                <tr>
                    <td colspan="5">{{ __('app.total_weight') }}:</td>
                    <td>{{ $goods['weight'] }}</td>
                </tr>
                <tr><td colspan="7"></td></tr>
                <tr><td colspan="7"></td></tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
