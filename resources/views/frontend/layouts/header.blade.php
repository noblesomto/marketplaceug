<!doctype html>
<html>
<head>
    <title>{{ $title }}</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/css/app.css','resources/js/app.js'])
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/fontawesome/css/all.min.css') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src='https://www.google.com/recaptcha/api.js' async defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-body text-gray-700 text-sm">
