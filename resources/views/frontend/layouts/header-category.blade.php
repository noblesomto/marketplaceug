<!doctype html>
<html>
<head>
    <title>{{ $cat->meta_title ?? $cat->category . ' | Marketplace Naija' }}</title>

    <meta charset="utf-8">
    <html lang="en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $cat->meta_description ?? 'Buy and sell in ' . $cat->category . ' on Marketplace Naija – Nigeria’s trusted online marketplace. Post free ads and trade safely today.' }}">

    <meta name="keywords" content="{{ $cat->keywords ?? 'Marketplace Naija, Buy & Sell Nigeria, ' . $cat->category . ', Nigeria classifieds, Online marketplace Nigeria' }}">

    <meta name="author" content="Marketplace Naija">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="{{ $cat->meta_title ?? $cat->category . ' | Marketplace Naija' }}">
    <meta property="og:description" content="{{ $cat->meta_description ?? 'Explore ads in ' . $cat->category . ' on Marketplace Naija.' }}">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $cat->meta_title ?? $cat->category . ' | Marketplace Naija' }}">
    <meta name="twitter:description" content="{{ $cat->meta_description ?? 'Explore ads in ' . $cat->category . ' on Marketplace Naija.' }}">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

    @include('frontend.layouts.header-links')
