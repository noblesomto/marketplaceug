<?php

namespace App\Services;

use App\Models\Advert;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * AdvertQueryService
 *
 * Shared query logic extracted from AdvertController and SearchFilter:
 *   - buildRelatedQuery()  — 4-level fallback for "related ads"
 *   - getBrandsForSubcat() — brand aggregation JOIN used across 5 controller methods
 */
class AdvertQueryService
{
    /**
     * Build the related-ads query with progressive fallback.
     *
     * Levels (returns the first that has results):
     *   1 — same category + subcategory + brand
     *   2 — same category + subcategory
     *   3 — same category
     *   4 — any active ads (last resort)
     *
     * Within the chosen level, ads whose title contains $keyword are ranked first.
     */
    public function buildRelatedQuery(Advert $ad, string $keyword)
    {
        $titleOrder = ['CASE WHEN ad_title LIKE ? THEN 1 ELSE 2 END ASC, RAND()', ['%' . $keyword . '%']];

        $base = fn() => Advert::with('firstImage', 'owner')
            ->where('id', '!=', $ad->id)
            ->activeNotRecentlySold();

        // Level 1: category + sub_category + brand
        if ($ad->brand) {
            $q = $base()
                ->where('category', $ad->category)
                ->where('sub_category', $ad->sub_category)
                ->where('brand', $ad->brand);

            if ((clone $q)->count() > 0) {
                return $q->orderByRaw(...$titleOrder);
            }
        }

        // Level 2: category + sub_category
        if ($ad->sub_category) {
            $q = $base()
                ->where('category', $ad->category)
                ->where('sub_category', $ad->sub_category);

            if ((clone $q)->count() > 0) {
                return $q->orderByRaw(...$titleOrder);
            }
        }

        // Level 3: category only
        $q = $base()->where('category', $ad->category);

        if ((clone $q)->count() > 0) {
            return $q->orderByRaw(...$titleOrder);
        }

        // Level 4: any active ads (last resort)
        return $base()->inRandomOrder();
    }

    /**
     * Get brands with active advert counts for a given subcategory.
     *
     * Used in: sub_category(), brand(), all_subcat() (AdvertController)
     *          location_subcat(), location_brand() (SearchFilter)
     */
    public function getBrandsForSubcat(int $subcatId): Collection
    {
        return DB::table('brands')
            ->leftJoin('adverts', 'brands.id', '=', 'adverts.brand')
            ->where('brands.subcat_id', $subcatId)
            ->where(function ($query) {
                $query->where('adverts.ad_status', 1)
                      ->where(function ($q) {
                          $q->where('adverts.sold_date', '>=', now()->subDays(30))
                            ->orWhereNull('adverts.sold_date');
                      });
            })
            ->select(
                'brands.id',
                'brands.brand',
                'brands.brand_slug',
                DB::raw('COUNT(adverts.id) as advert_count')
            )
            ->groupBy('brands.id', 'brands.brand', 'brands.brand_slug')
            ->orderBy('advert_count', 'desc')
            ->get();
    }
}
