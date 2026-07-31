<!doctype html>
<html lang="en">
<head>
    @php
        // seoTitle/seoDesc come from GeneratesSeoMeta::buildSeoMeta() and already
        // incorporate the parent category + location context (matches the on-page
        // H1). $subcat->meta_title is a static per-subcategory fallback only —
        // using it unconditionally here made every context (location, brand, etc.)
        // under this subcategory show the same bare title.
        $metaTitle = $seoTitle ?? $title ?? ($subcat->meta_title ?? $subcat->sub_category . ' | Marketplace Naija');
        $metaDesc  = $seoDesc  ?? ($subcat->meta_description ?? 'Buy and sell in ' . $subcat->sub_category . ' on Marketplace Naija – Nigeria’s trusted online marketplace. Post free ads and trade safely today.');
    @endphp
    <title>{{ $metaTitle }}</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $metaDesc }}">

    <meta name="keywords" content="{{ $subcat->keywords ?? 'Marketplace Naija, Buy & Sell Nigeria, ' . $subcat->sub_category . ', Nigeria classifieds, Online marketplace Nigeria' }}">

    <meta name="author" content="Marketplace Naija">
    <meta name="robots" content="{{ $metaRobots ?? 'index, follow' }}">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Marketplace Naija">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_NG">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

    @include('public.layouts.header-links')
