<!doctype html>
<html lang="en">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     @vite(['resources/css/app.css','resources/js/app.js'])


     <!-- SEO Meta Tags -->
    <meta name="description" content="Find anything you need in {{ $location }} on Marketplace Naija — Nigeria’s trusted platform to buy, sell, and trade with Buyer Protection. Post free ads today.">
    <meta name="keywords" content="Marketplace Naija, Buy & Sell in Nigeria, Post free ads in Nigeria, Online marketplace Nigeria, Secure deals in Nigeria, Safe online marketplace, Buy safely in Nigeria, Sell safely in Nigeria, Trade confidently in Nigeria, Nigeria classifieds website, Buy and sell goods online">
    <meta name="author" content="Marketplace Naija">


    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Marketplace Naija">
    <meta property="og:title" content="{{ $title ?? '| Marketplace Naija' }}">
    <meta property="og:description" content="Find anything you need in {{ $location }} on Marketplace Naija — Nigeria's trusted platform to buy, sell, and trade with Buyer Protection. Post free ads today.">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_NG">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? '| Marketplace Naija' }}">
    <meta name="twitter:description" content="Find anything you need in {{ $location }} on Marketplace Naija — Nigeria’s trusted platform to buy, sell, and trade with Buyer Protection. Post free ads today.">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

     <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

  @include('public.layouts.header-links')
