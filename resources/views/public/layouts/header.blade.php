<!doctype html>
<html lang="en">
<head>
    <title>{{ $title }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}" defer></script>
     {{-- Preload critical font --}}
<link rel="preload" href="{{ asset('assets/bootstrap-icons.woff2') }}" as="font" type="font/woff2" crossorigin>

@php
    // Fallback values used on all pages that don't pass SEO variables (homepage, ad detail, blog, etc.)
    $defaultDesc = "Marketplace Naija is Nigeria's trusted classifieds site. Post free ads to sell online fast or find cars, jobs, electronics, property and more near you.";
    $metaDesc      = $seoDesc      ?? $defaultDesc;
    $metaTitle     = $seoTitle     ?? $title ?? 'Marketplace Naija';
    $metaCanonical = $seoCanonical ?? url()->current();
@endphp

     <!-- SEO Meta Tags -->
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="keywords" content="post free ads Nigeria, buy and sell Nigeria, online marketplace Nigeria, classified ads Nigeria, free classifieds Nigeria, sell online Nigeria, buy cars Nigeria, jobs in Nigeria, electronics for sale Nigeria, property for sale Nigeria, Marketplace.ng, Marketplace Naija, local marketplace Nigeria, second hand items Nigeria">
    <meta name="author" content="Marketplace Naija">
    <meta name="robots" content="{{ $metaRobots ?? 'index, follow' }}">

    <!-- Canonical URL — always points to the clean URL (no query params/page numbers) -->
    <link rel="canonical" href="{{ $metaCanonical }}" />

    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Marketplace Naija">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ $metaCanonical }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_NG">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

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
            {{-- Mobile: preload thumb-sm (600×450) --}}
            <link rel="preload" as="image"
                  href="{{ $firstMedia->hasGeneratedConversion('thumb-sm') ? $firstMedia->getUrl('thumb-sm') : $firstMedia->getUrl('thumbnail') }}"
                  media="(max-width: 767px)"
                  fetchpriority="high">
            {{-- Desktop: preload thumb-md (800×600) --}}
            <link rel="preload" as="image"
                  href="{{ $firstMedia->hasGeneratedConversion('thumb-md') ? $firstMedia->getUrl('thumb-md') : $firstMedia->getUrl('thumbnail') }}"
                  media="(min-width: 768px)"
                  fetchpriority="high">
        @endif
    @endif

@include('public.layouts.header-links')



