<!doctype html>
<html>
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <html lang="en">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     @vite(['resources/css/app.css','resources/js/app.js'])


     <!-- SEO Meta Tags -->
    <meta name="description" content="Marketplace Naija is Nigeria’s trusted classifieds site. Post free ads to sell fast or find cars, jobs, electronics, property and more near you.">
    <meta name="keywords" content="post free ads Nigeria, buy and sell Nigeria, online marketplace Nigeria, classified ads Nigeria, free classifieds Nigeria, sell online Nigeria, buy cars Nigeria, jobs in Nigeria, electronics for sale Nigeria, property for sale Nigeria, Marketplace.ng, Marketplace Naija, local marketplace Nigeria, second hand items Nigeria">
    <meta name="author" content="Marketplace Naija">


    <!-- Open Graph / Facebook -->
    <meta property="og:title" content="{{ $title ?? 'Marketplace Naija' }}">
    <meta property="og:description" content="Marketplace Naija is Nigeria’s trusted classifieds site. Post free ads to sell fast or find cars, jobs, electronics, property and more near you.">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'Marketplace Naija' }}">
    <meta name="twitter:description" content="Marketplace Naija is Nigeria’s trusted classifieds site. Post free ads to sell fast or find cars, jobs, electronics, property and more near you.">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

     <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

  @include('frontend.layouts.header-links')
