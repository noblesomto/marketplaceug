<!doctype html>
<html lang="en">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     @vite(['resources/css/app.css','resources/js/app.js'])


     <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $seoDesc ?? ('Find anything you need in ' . $location . ' on Marketplace Uganda — Uganda\'s trusted platform to buy, sell, and trade with Buyer Protection. Post free ads today.') }}">
    <meta name="keywords" content="Marketplace Uganda, Buy & Sell in Uganda, Post free ads in Uganda, Online marketplace Uganda, Secure deals in Uganda, Safe online marketplace, Buy safely in Uganda, Sell safely in Uganda, Trade confidently in Uganda, Uganda classifieds website, Buy and sell goods online">
    <meta name="author" content="Marketplace Uganda">
    <meta name="robots" content="{{ $metaRobots ?? 'index, follow' }}">


    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Marketplace Uganda">
    <meta property="og:title" content="{{ $title ?? '| Marketplace Uganda' }}">
    <meta property="og:description" content="{{ $seoDesc ?? ('Find anything you need in ' . $location . ' on Marketplace Uganda — Uganda\'s trusted platform to buy, sell, and trade with Buyer Protection. Post free ads today.') }}">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_UG">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? '| Marketplace Uganda' }}">
    <meta name="twitter:description" content="{{ $seoDesc ?? ('Find anything you need in ' . $location . ' on Marketplace Uganda — Uganda\'s trusted platform to buy, sell, and trade with Buyer Protection. Post free ads today.') }}">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

     <!-- Canonical URL -->
    <link rel="canonical" href="{{ $seoCanonical ?? url()->current() }}" />

  @include('public.layouts.header-links')
