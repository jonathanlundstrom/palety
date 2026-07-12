@php
    use App\Enumerables\ImportCategory;
@endphp
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
<table>
    <tbody>
    <tr>
        <td>{{ __('app.import_list.category') }}</td>
        <td>{{ __('app.import_list.item_name') }}</td>
        <td>{{ __('app.import_list.num_pieces') }}</td>
        <td>{{ __('app.import_list.est_weight') }}</td>
        <td>{{ __('app.import_list.quantity') }}</td>
    </tr>

    @foreach ($data as $category => $goods)
        @php
            $categoryShown = false;
            $categoryRowSpan = count($goods['pallets']) + count($goods['parcels']);
        @endphp

        @foreach ($goods['pallets'] as $key => $pallet)
            <tr>
                @if ($key === 0 && $categoryShown === false)
                    <td rowspan="{{ $categoryRowSpan }}">{{ ImportCategory::from($category)->label() }}</td>
                    @php $categoryShown = true @endphp
                @endif

                <td>{{ $pallet['label_ua'] }}</td>
                <td>{{ $pallet['quantity'] }}</td>
                <td>{{ $pallet['weight'] }}</td>
                <td>{{ $pallet['quantity'] }} {{ mb_strtolower(trans_choice('app.pallet', $pallet['quantity'])) }}</td>
            </tr>
        @endforeach

        @foreach ($goods['parcels'] as $key => $parcel)
            <tr>
                @if ($key === 0 && $categoryShown === false)
                    <td rowspan="{{ $categoryRowSpan }}">{{ ImportCategory::from($category)->label() }}</td>
                    @php $categoryShown = true @endphp
                @endif

                <td>{{ $parcel['label_ua'] }}</td>
                <td>{{ $parcel['quantity'] }}</td>
                <td>{{ $parcel['weight'] }}</td>
                <td>{{ $parcel['quantity'] }} {{ trans_choice($parcel['unit'], $parcel['quantity']) }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
</body>
</html>
