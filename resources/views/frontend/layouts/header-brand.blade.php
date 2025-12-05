<!doctype html>
<html>
<head>
    <title>{{ $brand->meta_title ?? $brand->brand . ' | Marketplace Naija' }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $brand->meta_description ?? 'Buy and sell in ' . $brand->brand . ' on Marketplace Naija – Nigeria’s trusted online marketplace. Post free ads and trade safely today.' }}">

    <meta name="keywords" content="{{ $brand->meta_keywords ?? 'Marketplace Naija, Buy & Sell Nigeria, ' . $brand->brand . ', Nigeria classifieds, Online marketplace Nigeria' }}">

    <meta name="author" content="Marketplace Naija">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="{{ $brand->meta_title ?? $brand->brand . ' | Marketplace Naija' }}">
    <meta property="og:description" content="{{ $brand->meta_description ?? 'Explore ads in ' . $brand->brand . ' on Marketplace Naija.' }}">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $brand->meta_title ?? $brand->brand . ' | Marketplace Naija' }}">
    <meta name="twitter:description" content="{{ $brand->meta_description ?? 'Explore ads in ' . $brand->brand . ' on Marketplace Naija.' }}">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

    @include('frontend.layouts.header-links')
