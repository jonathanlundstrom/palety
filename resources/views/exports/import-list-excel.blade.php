@php
    use App\Enumerables\DeliveryType;
    use App\Enumerables\ImportCategory;use App\Enumerables\PalletType;
    use App\Enumerables\RecipientType;
@endphp
<html>
<head>
    <meta charset="UTF-8">
    <style>
        /* http://meyerweb.com/eric/tools/css/reset/
   v2.0 | 20110126
   License: none (public domain)
*/

        html, body, div, span, applet, object, iframe,
        h1, h2, h3, h4, h5, h6, p, blockquote, pre,
        a, abbr, acronym, address, big, cite, code,
        del, dfn, em, img, ins, kbd, q, s, samp,
        small, strike, strong, sub, sup, tt, var,
        b, u, i, center,
        dl, dt, dd, ol, ul, li,
        fieldset, form, label, legend,
        table, caption, tbody, tfoot, thead, tr, th, td,
        article, aside, canvas, details, embed,
        figure, figcaption, footer, header, hgroup,
        menu, nav, output, ruby, section, summary,
        time, mark, audio, video {
            margin: 0;
            padding: 0;
            border: 0;
            font-size: 100%;
            font: inherit;
            vertical-align: baseline;
        }
        /* HTML5 display-role reset for older browsers */
        article, aside, details, figcaption, figure,
        footer, header, hgroup, menu, nav, section {
            display: block;
        }
        body {
            line-height: 1;
        }
        ol, ul {
            list-style: none;
        }
        blockquote, q {
            quotes: none;
        }
        blockquote:before, blockquote:after,
        q:before, q:after {
            content: '';
            content: none;
        }
        table {
            border-collapse: collapse;
            border-spacing: 0;
        }

        table, th, td {
            border: 1px solid black;
        }
    </style>
</head>
<body>
<table width="100%">
    <tbody>
    <tr>
        <td width="20%">Категорія товару</td>
        <td width="20%">Найменування товарів</td>
        <td width="20%">Кількість місць</td>
        <td width="20%">Орієнтовна вага, кг</td>
        <td width="20%">Кількість, штук</td>
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
                <td>{{ number_format($pallet['quantity'], 2) }}</td>
                <td>{{ number_format($pallet['weight'], 2) }}</td>
                <td>{{ number_format($pallet['quantity'], 2) }} {{ mb_strtolower(trans_choice('app.pallet', $pallet['quantity'])) }}</td>
            </tr>
        @endforeach

        @foreach ($goods['parcels'] as $key => $parcel)
            <tr>
                @if ($key === 0 && $categoryShown === false)
                    <td rowspan="{{ $categoryRowSpan }}">{{ ImportCategory::from($category)->label() }}</td>
                    @php $categoryShown = true @endphp
                @endif

                <td>{{ $parcel['label_ua'] }}</td>
                <td>{{ number_format($parcel['quantity'], 2) }}</td>
                <td>{{ number_format($parcel['weight'], 2) }}</td>
                <td>{{ number_format($parcel['quantity'], 2) }} {{ $parcel['unit'] }}</td>
            </tr>
        @endforeach
    @endforeach
    </tbody>
</table>
</body>
</html>
