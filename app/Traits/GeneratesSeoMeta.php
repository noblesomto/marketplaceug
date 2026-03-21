<?php

namespace App\Traits;

trait GeneratesSeoMeta
{
    /**
     * Generate SEO meta title, description, and canonical URL for listing pages.
     *
     * Formula is driven by the category's seo_group:
     *   product  → "Buy {name} Online in {location} | {site}"
     *   property → "{name} for Rent & Sale in {location} | {site}"
     *   service  → "Find {name} in {location} | {site}"
     *
     * @param  string $name      Display name (category, subcategory, or "Brand Subcat")
     * @param  string $group     seo_group value from the categories table
     * @param  string $canonical Clean URL without query parameters or location prefix
     * @param  string $location  Filtered location — defaults to "Nigeria"
     * @return array{seoTitle: string, seoDesc: string, seoCanonical: string}
     */
    private function buildSeoMeta(string $name, string $group, string $canonical, string $location = 'Nigeria'): array
    {
        $site = config('global.site_name');

        switch ($group) {
            case 'property':
                $title = "{$name} for Rent & Sale in {$location} | {$site}";
                $desc  = "Find the best {$name} in {$location} on {$site}. Browse verified listings from agents and landlords. Schedule an inspection today.";
                break;

            case 'service':
                $title = "Find {$name} in {$location} | {$site}";
                $desc  = "Need {$name}? Connect with trusted professionals in {$location} on {$site}. Compare profiles, read reviews, and hire the best.";
                break;

            default: // product
                $title = "Buy {$name} Online in {$location} | {$site}";
                $desc  = "Looking for {$name}? Shop the widest selection of new and used {$name} on {$site}. Enjoy safe payments, verified sellers, and fast delivery to {$location}.";
                break;
        }

        return [
            'seoTitle'     => $title,
            'seoDesc'      => $desc,
            'seoCanonical' => $canonical,
        ];
    }
}
