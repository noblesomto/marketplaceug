<!doctype html>
<html>
<head>
    <title>{{ $title ?? ($ad->ad_title ?? 'Marketplace Naija') }}</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css','resources/js/app.js'])

    <!-- SEO Meta -->
    <meta name="description" content="{{ $ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160) }}">
    <meta name="keywords" content="{{ $ad->keyword ?? '' }}, London shortlets, luxury serviced apartments London, JJ Home Management">
    <meta name="author" content="Marketplace Naija">

    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}" />

    @php
        // Default fallback image
        $defaultImage = asset('frontend/images/Marketplace-Naija.png');

        // Featured image (if available)
        $featuredImage = ($ad->firstImage && $ad->firstImage->image)
            ? asset('uploads/images/' . $ad->firstImage->image)
            : $defaultImage;

        // Collect all images
        $images = [];
        if ($ad->firstImage && $ad->firstImage->image) {
            $images[] = $featuredImage;
        }

        if (!empty($ad->images) && $ad->images->count()) {
            foreach ($ad->images as $img) {
                if (!empty($img->image)) {
                    $images[] = asset('uploads/more-images/' . $img->image);
                }
            }
        }

        if (empty($images)) {
            $images[] = $defaultImage;
        }

        // Schema data
        $schema = [
            "@context" => "https://schema.org",
            "@type" => "Product",
            "name" => $ad->ad_title ?? 'Marketplace Naija',
            "image" => $images,
            "description" => Str::limit(strip_tags($ad->description ?? ''), 200),
            "sku" => $ad->ad_id ?? 'MPN-' . rand(1000, 9999),
            "brand" => [
                "@type" => "Organization",
                "name" => "Marketplace Naija"
            ],
            "offers" => [
                "@type" => "Offer",
                "url" => url()->current(),
                "priceCurrency" => "NGN",
                "price" => $ad->price ?? '0.00',
                "availability" => "https://schema.org/InStock"
            ]
        ];
    @endphp

    <!-- Open Graph -->
    <meta property="og:title" content="{{ ($ad->ad_title ?? 'Marketplace Naija') . ' | Marketplace Naija' }}">
    <meta property="og:description" content="{{ $ad->meta_description ?? Str::limit(strip_tags($ad->description ?? ''), 160) }}">
    <meta property="og:image" content="{{ $featuredImage }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ ($ad->ad_title ?? $ad->ad_title ?? 'Marketplace Naija') . ' | JJ Home Management' }}">
    <meta name="twitter:description" content="{{ $ad->meta_description ?? $ad->meta_description ?? Str::limit(strip_tags($ad->description ?? $ad->description ?? ''), 160) }}">
    <meta name="twitter:image" content="{{ $featuredImage }}">

    <!-- JSON-LD Schema -->
    <script type="application/ld+json">
    {!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>


  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('frontend/images/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('frontend/css/fontawesome/css/all.min.css') }}" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src='https://www.google.com/recaptcha/api.js' async defer></script>
  <meta name="csrf-token" content="{{ csrf_token() }}">
<script type="text/javascript">
   (function(c,l,a,r,i,t,y){
       c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
       t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
       y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
   })(window, document, "clarity", "script", "su2vqghfhw");
</script>
</head>
<body class="bg-body text-gray-700 text-sm">
