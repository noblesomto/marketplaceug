<?php

namespace App\Services;

use App\Models\Advert;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class GoogleMerchantFeedGenerator
{
    public function generate()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss/>');
        $xml->addAttribute('version', '2.0');
        $xml->addAttribute('xmlns:g', 'http://base.google.com/ns/1.0');

        $channel = $xml->addChild('channel');
        $channel->addChild('title', config('app.name') . ' Product Feed');
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Latest adverts from ' . config('app.name'));

        $adverts = Advert::where('ad_status', 'active')
            ->where('sold', 'No')
            ->whereNotNull('price')
            ->take(5000)
            ->get();

        foreach ($adverts as $ad) {
            $item = $channel->addChild('item');
            $item->addChild('g:id', htmlspecialchars($ad->ad_id));
            $item->addChild('title', htmlspecialchars($ad->ad_title));
            $item->addChild('description', htmlspecialchars(strip_tags($ad->description)));
            $item->addChild('link', url("{$ad->state_slug}/{$ad->title_slug}/{$ad->ad_id}"));

            // ✅ Updated image logic using Spatie
            if ($ad->hasMedia('images')) {
                $imageUrl = $ad->getFirstMediaUrl('images', 'thumbnail');
            } else {
                $imageUrl = asset('frontend/images/default.png');
            }
            $item->addChild('g:image_link', $imageUrl);

            // ✅ You can also add extra images if available
            foreach ($ad->getMedia('images') as $media) {
                $item->addChild('g:additional_image_link', $media->getUrl('thumbnail'));
            }

            $priceValue = (float) preg_replace('/[^\d.]/', '', $ad->price); // remove commas or symbols
            $item->addChild('g:price', number_format($priceValue, 2) . ' NGN');
            $availability = (strtolower($ad->sold) === 'yes') ? 'out of stock' : 'in stock';
            $item->addChild('g:availability', $availability);
            $item->addChild('g:condition', strtolower($ad->item_condition ?? 'new'));

            if (!empty($ad->brand)) {
                $item->addChild('g:brand', htmlspecialchars($ad->brand));
            }

            if (!empty($ad->category)) {
                $item->addChild('g:product_type', htmlspecialchars($ad->category));
            }

            $shipping = $item->addChild('g:shipping');
            $shipping->addChild('g:country', 'NG');

            // Determine shipping service text
            if (strtolower($ad->shipment) === 'pickup') {
                $shipping->addChild('g:service', 'Pickup');
            } elseif (strtolower($ad->shipment) === 'Ship') {
                $shipping->addChild('g:service', 'Standard Shipping');
            } else {
                $shipping->addChild('g:service', 'Standard');
            }

            $shipping->addChild('g:price', '0 NGN'); // or use real shipping cost if available
        }

        $filePath = 'feeds/google_merchant.xml';
        Storage::disk('public')->put($filePath, $xml->asXML());

        return public_path('storage/' . $filePath);
    }
}
