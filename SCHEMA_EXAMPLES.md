# JSON-LD Schema Examples - Marketplace Naija

## 1. Homepage - Organization Schema
```json
{
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "Marketplace Naija",
    "alternateName": "Marketplace Nigeria",
    "url": "https://www.marketplace.ng",
    "logo": "https://www.marketplace.ng/frontend/images/Marketplace-Naija.png",
    "sameAs": [
        "https://www.facebook.com/marketplacenaija",
        "https://twitter.com/marketplacenaija",
        "https://www.instagram.com/marketplacenaija"
    ],
    "contactPoint": {
        "@type": "ContactPoint",
        "telephone": "+234 8034 814 561",
        "contactType": "Customer Service",
        "areaServed": "NG",
        "availableLanguage": "English"
    }
}
```

## 2. Homepage - WebSite Schema
```json
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "Marketplace Naija",
    "alternateName": "Marketplace Nigeria",
    "url": "https://www.marketplace.ng",
    "potentialAction": {
        "@type": "SearchAction",
        "target": {
            "@type": "EntryPoint",
            "urlTemplate": "https://www.marketplace.ng/search?q={search_term_string}"
        },
        "query-input": "required name=search_term_string"
    }
}
```

## 3. Product Page - Product Schema (Clean, No Dates/IDs)
```json
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "iPhone 13 Pro 128GB Graphite",
    "image": [
        "https://www.marketplace.ng/storage/media/1/conversions/large.webp",
        "https://www.marketplace.ng/storage/media/2/conversions/large.webp"
    ],
    "description": "iPhone 13 Pro in excellent condition, 128GB storage, graphite color. Battery health 95%. Comes with original box and charger.",
    "brand": {
        "@type": "Brand",
        "name": "Apple"
    },
    "offers": {
        "@type": "Offer",
        "url": "https://www.marketplace.ng/advert/iphone-13-pro-128gb",
        "priceCurrency": "NGN",
        "price": "450000",
        "availability": "https://schema.org/InStock",
        "itemCondition": "https://schema.org/UsedCondition"
    }
}
```

## 4. Blog Post - Article Schema (No Dates/IDs)
```json
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "10 Tips for Safe Online Trading in Nigeria",
    "image": [
        "https://www.marketplace.ng/storage/blog/featured-image.webp"
    ],
    "description": "Learn essential tips for buying and selling safely on online marketplaces. From verifying sellers to secure payment methods.",
    "author": {
        "@type": "Organization",
        "name": "Marketplace Naija"
    },
    "publisher": {
        "@type": "Organization",
        "name": "Marketplace Naija",
        "logo": {
            "@type": "ImageObject",
            "url": "https://www.marketplace.ng/frontend/images/Marketplace-Naija.png"
        }
    },
    "mainEntityOfPage": {
        "@type": "WebPage",
        "@id": "https://www.marketplace.ng/blog/safe-online-trading-tips"
    }
}
```

## 5. Optional - BreadcrumbList Schema (Product Page)
```json
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@type": "ListItem",
            "position": 1,
            "name": "Home",
            "item": "https://www.marketplace.ng"
        },
        {
            "@type": "ListItem",
            "position": 2,
            "name": "Electronics",
            "item": "https://www.marketplace.ng/category/electronics"
        },
        {
            "@type": "ListItem",
            "position": 3,
            "name": "Mobile Phones",
            "item": "https://www.marketplace.ng/category/electronics/mobile-phones"
        },
        {
            "@type": "ListItem",
            "position": 4,
            "name": "iPhone 13 Pro 128GB Graphite"
        }
    ]
}
```

## 6. Optional - AggregateRating Schema (If Reviews Exist)
```json
{
    "@context": "https://schema.org",
    "@type": "Product",
    "name": "Product Name",
    "aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": "4.5",
        "reviewCount": "24"
    }
}
```

## Key Differences from Previous Implementation

### ❌ REMOVED (Previous)
```json
{
    "sku": "AD-12345",              // ❌ Exposes internal ID
    "identifier": "MPN-12345",      // ❌ Exposes internal ID
    "datePosted": "2024-01-15",     // ❌ Exposes upload date
    "uploadDate": "2024-01-15",     // ❌ Exposes upload date
    "datePublished": "2024-01-15",  // ❌ Exposes publish date
    "dateModified": "2024-01-20",   // ❌ Exposes modification date
    "brand": {
        "@type": "Organization",     // ❌ Wrong type for product brand
        "name": "Brand Name"
    }
}
```

### ✅ KEPT (Current)
```json
{
    "name": "Product Name",          // ✅ Essential
    "image": ["url1", "url2"],       // ✅ Essential for rich results
    "description": "...",            // ✅ Essential for SEO
    "brand": {
        "@type": "Brand",            // ✅ Correct type
        "name": "Brand Name"
    },
    "offers": {
        "price": "450000",           // ✅ Shows price in results
        "priceCurrency": "NGN",      // ✅ Shows currency
        "availability": "InStock",   // ✅ Shows availability
        "itemCondition": "Used"      // ✅ Shows condition
    }
}
```

## Meta Tags Summary (All Pages)

### Essential Meta Tags
```html
<!-- Title -->
<title>Page Title | Marketplace Naija</title>

<!-- Basic SEO -->
<meta name="description" content="Page description here">
<meta name="keywords" content="relevant, keywords, here">
<meta name="author" content="Marketplace Naija">

<!-- Canonical URL -->
<link rel="canonical" href="https://www.marketplace.ng/current-page" />

<!-- Open Graph -->
<meta property="og:site_name" content="Marketplace Naija">
<meta property="og:title" content="Page Title | Marketplace Naija">
<meta property="og:description" content="Page description">
<meta property="og:image" content="https://www.marketplace.ng/image.png">
<meta property="og:url" content="https://www.marketplace.ng/current-page">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_NG">

<!-- Twitter Card -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="Page Title | Marketplace Naija">
<meta name="twitter:description" content="Page description">
<meta name="twitter:image" content="https://www.marketplace.ng/image.png">
```

## Google Rich Results Preview

### Product Page
```
Marketplace Naija
iPhone 13 Pro 128GB Graphite
₦450,000 · In stock · Used condition
iPhone 13 Pro in excellent condition, 128GB storage, graphite color.
Battery health 95%. Comes with original box and charger.
```

### Blog Page
```
Marketplace Naija
10 Tips for Safe Online Trading in Nigeria
Learn essential tips for buying and selling safely on online
marketplaces. From verifying sellers to secure payment methods.
```

### Category Page
```
Marketplace Naija
Electronics for Sale in Nigeria
Buy and sell electronics on Marketplace Naija – Nigeria's trusted
online marketplace. Post free ads and trade safely today.
```
