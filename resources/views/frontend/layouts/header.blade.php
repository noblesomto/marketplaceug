<!doctype html>
<html>
<head>
    <title>{{ $title }}</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  @vite(['resources/css/app.css','resources/js/app.js'])

     <!-- SEO Meta Tags -->
    <meta name="description" content="Marketplace Naija – Nigeria’s trusted online marketplace. Buy, sell, and trade confidently with Buyer Protection on every transaction. Post free ads today">
    <meta name="keywords" content="Marketplace Naija, Buy & Sell in Nigeria, Post free ads in Nigeria, Online marketplace Nigeria, Secure deals in Nigeria, Safe online marketplace, Buy safely in Nigeria, Sell safely in Nigeria, Trade confidently in Nigeria, Nigeria classifieds website, Buy and sell goods online">
    <meta name="author" content="Marketplace Naija">

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="{{ $title ?? 'Marketplace Naija' }}">
    <meta property="og:description" content="Marketplace Naija – Nigeria’s trusted online marketplace. Buy, sell, and trade confidently with Buyer Protection on every transaction. Post free ads today">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Marketplace Naija' }}">
    <meta name="twitter:description" content="Marketplace Naija – Nigeria’s trusted online marketplace. Buy, sell, and trade confidently with Buyer Protection on every transaction. Post free ads today">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">


  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/fontawesome/css/all.min.css') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src='https://www.google.com/recaptcha/api.js' async defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
<script type="text/javascript">
   (function(c,l,a,r,i,t,y){
       c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
       t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
       y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
   })(window, document, "clarity", "script", "su2vqghfhw");
</script>
</head>
<body class="bg-body text-gray-700 text-sm">
