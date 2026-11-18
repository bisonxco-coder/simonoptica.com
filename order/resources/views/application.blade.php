<!DOCTYPE html>
<html lang="en">
<head>
    <?php setcookie("htaccessRule", config('environment.htaccess_value'), 0, '/'); ?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name') }}</title>

    <!-- Styles CSS -->
    <link rel="stylesheet" href="{{ asset(mix('css/app.css')) }}?v={{ config('environment.compiled_version') }}">
    @if (File::exists('custom/app.css'))
        <link rel="stylesheet" href="{{ asset('custom/app.css') }}">
    @endif

    <!-- Favicon -->
    @if (File::exists('custom/favicon.ico'))
        <link rel="shortcut icon" href="{{ asset('custom/favicon.ico') }}">
    @else
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    @endif
</head>
<body translate="no">
    <noscript>
        <strong>We're sorry but LensWare doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
    </noscript>

    <div id="app"></div>

    <!-- Javascript -->
    <script src="{{ asset(mix('js/app.js')) }}?v={{ config('environment.compiled_version') }}"></script>
    @if (File::exists('custom/app.js'))
        <script src="{{ asset('custom/app.js') }}"></script>
    @endif
</body>
</html>
