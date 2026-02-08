<!doctype html>
<html lang="en">
<head>
    <title>Sell Online in Nigeria | Sell Phones, Property & Cars Fast | Marketplace.ng</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}" defer></script>
     {{-- Preload critical font --}}
<link rel="preload" href="{{ asset('assets/bootstrap-icons.woff2') }}" as="font" type="font/woff2" crossorigin>

{{-- Preload LCP image if you know it --}}
<link rel="preload" href="{{ asset('images/logo.png') }}" as="image">


     <!-- SEO Meta Tags -->
    <meta name="description" content="Start selling online in Nigeria for free. Reach millions of buyers instantly. The safest place to sell phones, property, cars, and electronics on Marketplace.ng.">
    <meta name="keywords" content="post free ads Nigeria, buy and sell Nigeria, online marketplace Nigeria, classified ads Nigeria, free classifieds Nigeria, sell online Nigeria, buy cars Nigeria, jobs in Nigeria, electronics for sale Nigeria, property for sale Nigeria, Marketplace.ng, Marketplace Naija, local marketplace Nigeria, second hand items Nigeria">
    <meta name="author" content="Marketplace Naija">


    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Marketplace Naija">
    <meta property="og:title" content="Sell Online in Nigeria | Sell Phones, Property & Cars Fast | Marketplace.ng">
    <meta property="og:description" content="Start selling online in Nigeria for free. Reach millions of buyers instantly. The safest place to sell phones, property, cars, and electronics on Marketplace.ng.">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_NG">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sell Online in Nigeria | Sell Phones, Property & Cars Fast | Marketplace.ng">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

     <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

<!-- JSON-LD Organization Schema -->
@php
$organizationSchema = [
    "@context" => "https://schema.org",
    "@type" => "Organization",
    "name" => "Marketplace Naija",
    "alternateName" => "Marketplace Nigeria",
    "url" => config('app.url'),
    "logo" => asset('frontend/images/Marketplace-Naija.png'),
    "sameAs" => [
        "https://www.facebook.com/marketplacenaija",
        "https://twitter.com/marketplacenaija",
        "https://www.instagram.com/marketplacenaija"
    ]
];

if (config('global.site_phone')) {
    $organizationSchema["contactPoint"] = [
        "@type" => "ContactPoint",
        "telephone" => config('global.site_phone'),
        "contactType" => "Customer Service",
        "areaServed" => "NG",
        "availableLanguage" => "English"
    ];
}
@endphp
<script type="application/ld+json">
{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

<!-- JSON-LD WebSite Schema -->
@php
$websiteSchema = [
    "@context" => "https://schema.org",
    "@type" => "WebSite",
    "name" => "Marketplace Naija",
    "alternateName" => "Marketplace Nigeria",
    "url" => config('app.url'),
    "potentialAction" => [
        "@type" => "SearchAction",
        "target" => [
            "@type" => "EntryPoint",
            "urlTemplate" => config('app.url') . "/search?q={search_term_string}"
        ],
        "query-input" => "required name=search_term_string"
    ]
];
@endphp
<script type="application/ld+json">
{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>

    <!-- Preload LCP Image - Add this to your <head> section -->
    @if(isset($gallery[0]))
        @php
            $firstMedia = $gallery[0]->getFirstMedia('images');
        @endphp
        @if($firstMedia)
            <link rel="preload" as="image"
                  href="{{ $firstMedia->hasGeneratedConversion('thumb-sm') ? $firstMedia->getUrl('thumb-sm') : $firstMedia->getUrl('thumbnail') }}"
                  fetchpriority="high">
        @endif
    @endif

    @if(isset($listings[0]))
        @php
            $firstImage = $listings[0]->getFirstMedia('images');
        @endphp
        @if($firstImage)
            <link rel="preload" as="image"
                  href="{{ $firstImage->hasGeneratedConversion('thumb-sm') ? $firstImage->getUrl('thumb-sm') : $firstImage->getUrl('thumbnail') }}"
                  fetchpriority="high"
                  media="(max-width: 640px)">
        @endif
    @endif  {{-- This was missing --}}

@include('frontend.layouts.header-links')



