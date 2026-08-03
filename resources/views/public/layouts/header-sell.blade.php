<!doctype html>
<html lang="en">
<head>
    <title>Sell Online in Uganda | Sell Phones, Property & Cars Fast | Marketplace Uganda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css'])
    <script type="module" src="{{ Vite::asset('resources/js/app.js') }}" defer></script>
     {{-- Preload critical font --}}
<link rel="preload" href="{{ asset('assets/bootstrap-icons.woff2') }}" as="font" type="font/woff2" crossorigin>

{{-- Preload LCP image if you know it --}}
<link rel="preload" href="{{ asset('images/logo.png') }}" as="image">


     <!-- SEO Meta Tags -->
    <meta name="description" content="Start selling online in Uganda for free. Reach millions of buyers instantly. The safest place to sell phones, property, cars, and electronics on Marketplace Uganda.">
    <meta name="keywords" content="post free ads Uganda, buy and sell Uganda, online marketplace Uganda, classified ads Uganda, free classifieds Uganda, sell online Uganda, buy cars Uganda, jobs in Uganda, electronics for sale Uganda, property for sale Uganda, Marketplace UG, Marketplace Uganda, local marketplace Uganda, second hand items Uganda">
    <meta name="author" content="Marketplace Uganda">


    <!-- Open Graph / Facebook -->
    <meta property="og:site_name" content="Marketplace Uganda">
    <meta property="og:title" content="Sell Online in Uganda | Sell Phones, Property & Cars Fast | Marketplace Uganda">
    <meta property="og:description" content="Start selling online in Uganda for free. Reach millions of buyers instantly. The safest place to sell phones, property, cars, and electronics on Marketplace Uganda.">
    <meta property="og:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:locale" content="en_UG">

    <!-- Twitter Meta -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sell Online in Uganda | Sell Phones, Property & Cars Fast | Marketplace Uganda">
    <meta name="twitter:image" content="{{ asset('frontend/images/Marketplace-Naija.png') }}">

     <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

<!-- JSON-LD Organization Schema -->
@php
$organizationSchema = [
    "@context" => "https://schema.org",
    "@type" => "Organization",
    "name" => "Marketplace Uganda",
    "alternateName" => "Marketplace UG",
    "url" => config('app.url'),
    "logo" => asset('frontend/images/Marketplace-Naija.png'),
    "sameAs" => [
        "https://www.facebook.com/marketplaceuganda",
        "https://twitter.com/marketplaceuganda",
        "https://www.instagram.com/marketplaceuganda"
    ]
];

if (config('global.site_phone')) {
    $organizationSchema["contactPoint"] = [
        "@type" => "ContactPoint",
        "telephone" => config('global.site_phone'),
        "contactType" => "Customer Service",
        "areaServed" => "UG",
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
    "name" => "Marketplace Uganda",
    "alternateName" => "Marketplace UG",
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

@include('public.layouts.header-links')



