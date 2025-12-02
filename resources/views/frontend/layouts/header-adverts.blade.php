<!doctype html>
<html lang="en">
<head>
    <title>{{ $title ?? ($ad->ad_title ?? 'Marketplace Naija') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- SEO Meta -->
    <meta name="description" content="{{ $ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160) }}">
    <meta name="keywords" content="{{ $ad->keyword ?? '' }}">
    <meta name="author" content="Marketplace Naija">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    @php
        // Get social media optimized image using existing 'large' conversion
        $featuredImage = $ad->getSocialImageUrl();
        $allImages = $ad->getAllImagesForSchema();
        $imageDimensions = $ad->getSocialImageDimensions();

        // Schema data
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $ad->ad_title ?? 'Marketplace Naija',
            "image" => $allImages,
            "description" => Str::limit(strip_tags($ad->description ?? ''), 200),
            "sku" => $ad->ad_id ?? 'MPN-' . rand(1000, 9999),
            "brand" => [
                "@type" => "Organization",
                "name" => $ad->brands->brand ?? "Marketplace Naija"
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => url()->current(),
                "priceCurrency" => "NGN",
                "price" => $ad->price ?? '0.00',
                "availability" => $ad->sold == 'Yes' ? "https://schema.org/OutOfStock" : "https://schema.org/InStock",
                "itemCondition" => "https://schema.org/" . ($ad->item_condition == 'New' ? 'NewCondition' : 'UsedCondition')
            ]
        ];
    @endphp

    <!-- Open Graph Meta Tags -->
    <meta property="og:site_name" content="Marketplace Naija">
    <meta property="og:title" content="{{ $ad->ad_title ?? 'Marketplace Naija' }} | Marketplace Naija">
    <meta property="og:description" content="{{ $ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160) }}">
    <meta property="og:image" content="{{ $featuredImage }}">
    <meta property="og:image:secure_url" content="{{ $featuredImage }}">
    <meta property="og:image:width" content="{{ $imageDimensions['width'] }}">
    <meta property="og:image:height" content="{{ $imageDimensions['height'] }}">
    <meta property="og:image:alt" content="{{ $ad->ad_title ?? 'Marketplace Naija' }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="product">
    <meta property="og:locale" content="en_NG">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ad->ad_title ?? 'Marketplace Naija' }} | Marketplace Naija">
    <meta name="twitter:description" content="{{ $ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160) }}">
    <meta name="twitter:image" content="{{ $featuredImage }}">
    <meta name="twitter:image:alt" content="{{ $ad->ad_title ?? 'Marketplace Naija' }}">

    <!-- Additional Product Meta -->
    <meta property="product:price:amount" content="{{ $ad->price ?? '0.00' }}">
    <meta property="product:price:currency" content="NGN">
    <meta property="product:availability" content="{{ $ad->sold == 'Yes' ? 'out of stock' : 'in stock' }}">
    @if($ad->item_condition)
    <meta property="product:condition" content="{{ strtolower($ad->item_condition) }}">
    @endif

    <!-- JSON-LD Schema -->
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('frontend/css/fontawesome/css/all.min.css') }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src='https://www.google.com/recaptcha/api.js' async defer></script>

    <!-- Include SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Microsoft Clarity -->
    <script type="text/javascript">
       (function(c,l,a,r,i,t,y){
           c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
           t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
           y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
       })(window, document, "clarity", "script", "su2vqghfhw");
    </script>

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-TPBJ5F0GJP"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'G-TPBJ5F0GJP');
    </script>

    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-PVDT4VHH');</script>

    <!-- Google Ads -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=AW-17541624328"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', 'AW-17541624328');
    </script>

    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-4998736645213032"
         crossorigin="anonymous"></script>
</head>
<body class="bg-body text-gray-700 text-sm">
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PVDT4VHH"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
