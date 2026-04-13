<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title>{{ isset($title) ? $title . ' – ' . config('app.name') : config('app.name') }}</title>
<meta name="csrf-token" content="{{ csrf_token() }}">

{{-- Generic favicon and theme style --}}
<link rel="manifest" href="/site.webmanifest">
<link rel="shortcut icon" href="/images/favicon.ico">
<link rel="icon" type="image/x-icon" href="/images/favicon.ico">
<link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
<link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="96x96" href="/images/favicon-96x96.png">
<link rel="icon" type="image/png" sizes="128x128" href="/images/favicon-128x128.png">

{{-- Apple devices --}}
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
<link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">

{{-- Android devices --}}
<link rel="icon" type="image/png" sizes="192x192" href="/images/android-chrome-192x192.png">
<link rel="icon" type="image/png" sizes="512x512" href="/images/android-chrome-512x512.png">

{{-- Windows devices --}}
<meta name="msapplication-TileImage" content="/images/mstile-150x150-png">
<meta name="msapplication-TileColor" content="#ffffff">

{{-- Theme color --}}
<meta name="theme-color" content="#ffffff">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
