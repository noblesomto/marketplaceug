<!doctype html>
<html>
<head>
    <title>{{ $cat->meta_title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     @vite(['resources/css/app.css','resources/js/app.js'])


     <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $cat->meta_description }}">
    <meta name="keywords" content="Marketplace Naija, Buy & Sell in Nigeria, Post free ads in Nigeria, Online marketplace Nigeria, Secure deals in Nigeria, Safe online marketplace, Buy safely in Nigeria, Sell safely in Nigeria, Trade confidently in Nigeria, Nigeria classifieds website, Buy and sell goods online">
    <meta name="author" content="Marketplace Naija">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />


    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="{{ $cat->meta_title ?? 'Marketplace Naija' }}">
    <meta property="og:description" content="{{ $cat->meta_description }}">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $cat->meta_title ?? 'Marketplace Naija' }}">
    <meta name="twitter:description" content="{{ $cat->meta_description }}">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">


  @include('frontend.layouts.header-links')
