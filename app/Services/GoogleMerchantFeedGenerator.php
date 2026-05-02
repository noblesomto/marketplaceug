<?php
namespace App\Services;

use App\Models\Advert;
use Illuminate\Support\Facades\Storage;
use SimpleXMLElement;

class GoogleMerchantFeedGenerator
{
    public function generate()
    {
        $xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:g="http://base.google.com/ns/1.0"></rss>');
        
        $channel = $xml->addChild('channel');
        $channel->addChild('title', config('app.name') . ' Product Feed');
        $channel->addChild('link', url('/'));
        $channel->addChild('description', 'Latest adverts from ' . config('app.name'));
        
        $adverts = Advert::where('ad_status', 'active')
            ->where('sold', 'No')
            ->whereRaw("LOWER(TRIM(buy_direct)) = 'yes'")
            ->orderByDesc('created_at')
            ->limit(5000)
            ->get();
        
        foreach ($adverts as $ad) {
            $item = $channel->addChild('item');
            
            // Use addChild with namespace URL for Google attributes
            $item->addChild('id', htmlspecialchars($ad->ad_id), 'http://base.google.com/ns/1.0');
            $item->addChild('title', htmlspecialchars($ad->ad_title ?? 'Untitled Product'));
            $item->addChild('description', htmlspecialchars(strip_tags($ad->description ?? 'No description available.')));
            $item->addChild('link', url("{$ad->state_slug}/{$ad->title_slug}/{$ad->ad_id}"));
            
            // Image handling
            if ($ad->hasMedia('images')) {
                $imageUrl = $ad->getFirstMediaUrl('images', 'thumbnail');
            } else {
                $imageUrl = asset('frontend/images/default.png');
            }
            $item->addChild('image_link', $imageUrl, 'http://base.google.com/ns/1.0');
            
            // Price - must be valid and greater than 0
            $priceValue = 0.00;
            if (!empty($ad->price)) {
                $priceValue = (float) preg_replace('/[^\d.]/', '', $ad->price);
            }
            // Google requires price > 0
            if ($priceValue <= 0) {
                $priceValue = 1.00; // Minimum price
            }
            $item->addChild('price', number_format($priceValue, 2, '.', '') . ' NGN', 'http://base.google.com/ns/1.0');
            
            // Availability
            $sold = strtolower(trim($ad->sold ?? 'No'));
            $availability = ($sold === 'yes') ? 'out of stock' : 'in stock';
            $item->addChild('availability', $availability, 'http://base.google.com/ns/1.0');
            
            // Condition - must be 'new', 'refurbished', or 'used'
            $condition = strtolower($ad->item_condition ?? 'new');
            if (!in_array($condition, ['new', 'refurbished', 'used'])) {
                $condition = 'used';
            }
            $item->addChild('condition', $condition, 'http://base.google.com/ns/1.0');
            
            // Brand
            if (!empty($ad->brand)) {
                $item->addChild('brand', htmlspecialchars($ad->brand), 'http://base.google.com/ns/1.0');
            }
            
            // Category
            if (!empty($ad->category)) {
                $item->addChild('product_type', htmlspecialchars($ad->category), 'http://base.google.com/ns/1.0');
            }
            
            // Google product category (you may want to map your categories to Google's taxonomy)
            $item->addChild('google_product_category', 'Electronics', 'http://base.google.com/ns/1.0');
            
            // Custom label for buy-direct products — used in Google Ads to bid separately
            if (strtolower(trim($ad->buy_direct ?? '')) === 'yes') {
                $item->addChild('custom_label_0', 'buy direct', 'http://base.google.com/ns/1.0');
            }

            // Shipping
            $shipping = $item->addChild('shipping', null, 'http://base.google.com/ns/1.0');
            $shipping->addChild('country', 'NG', 'http://base.google.com/ns/1.0');
            
            $shipment = strtolower(trim($ad->shipment ?? 'standard'));
            if ($shipment === 'pickup') {
                $shipping->addChild('service', 'Pickup', 'http://base.google.com/ns/1.0');
            } elseif ($shipment === 'ship') {
                $shipping->addChild('service', 'Standard Shipping', 'http://base.google.com/ns/1.0');
            } else {
                $shipping->addChild('service', 'Standard', 'http://base.google.com/ns/1.0');
            }
            $shipping->addChild('price', '0.00 NGN', 'http://base.google.com/ns/1.0');
        }
        
        $filePath = 'uploads/feeds/google_merchant.xml';
        Storage::disk('public')->put($filePath, $xml->asXML());

        return storage_path('app/public/' . $filePath);
    }
}