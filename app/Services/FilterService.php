<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * FilterService - Centralized filtering logic for adverts
 *
 * Extracts common filtering patterns from AdvertController and SearchFilter
 * to reduce code duplication and improve maintainability.
 */
class FilterService
{
    /**
     * Apply base filters (category, subcategory, brand, location)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyContextFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('sub_category')) {
            $query->where('sub_category', $request->sub_category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->filled('location')) {
            $query->where('state', $request->location);
        }

        return $query;
    }

    /**
     * Apply price filters (min, max, range)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyPriceFilters(Builder $query, Request $request): Builder
    {
        if ($request->filled('min')) {
            $query->where('price', '>=', (int) $request->input('min'));
        }

        if ($request->filled('max')) {
            $query->where('price', '<=', (int) $request->input('max'));
        }

        if ($request->filled('range')) {
            $this->applyPriceRange($query, $request->input('range'));
        }

        return $query;
    }

    /**
     * Apply predefined price ranges
     *
     * @param Builder $query
     * @param string $range
     * @return Builder
     */
    protected function applyPriceRange(Builder $query, string $range): Builder
    {
        switch ($range) {
            case 'under_20k':
                $query->where('price', '<', 20000);
                break;
            case '20k_120k':
                $query->whereBetween('price', [20000, 120000]);
                break;
            case '120k_1m':
                $query->whereBetween('price', [120000, 1000000]);
                break;
            case '1m_10m':
                $query->whereBetween('price', [1000000, 10000000]);
                break;
            case 'above_10m':
                $query->where('price', '>', 10000000);
                break;
        }

        return $query;
    }

    /**
     * Apply seller filter
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applySellerFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('seller')) {
            $sellerType = $request->seller;

            if ($sellerType === 'verified') {
                $query->whereHas('user', function ($q) {
                    $q->where('verified', 'yes');
                });
            } elseif ($sellerType === 'unverified') {
                $query->whereHas('user', function ($q) {
                    $q->where('verified', 'no');
                });
            }
        }

        return $query;
    }

    /**
     * Apply buy direct filter
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyBuyDirectFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('buydirect')) {
            $buyDirect = $request->buydirect;

            if ($buyDirect === 'yes') {
                $query->where('buy_direct', 'Yes');
            } elseif ($buyDirect === 'no') {
                $query->where('buy_direct', 'No');
            }
        }

        return $query;
    }

    /**
     * Apply car detail filters (condition, fuel type, transmission, registration)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyCarFilters(Builder $query, Request $request): Builder
    {
        $query->whereHas('carDetail', function ($q) use ($request) {
            if ($request->filled('condition')) {
                $q->where('condition', $request->condition);
            }

            if ($request->filled('fuel_type')) {
                $q->where('fuel_type', $request->fuel_type);
            }

            if ($request->filled('transmission')) {
                $q->where('transmission', $request->transmission);
            }

            if ($request->filled('registration')) {
                $q->where('registration', $request->registration);
            }
        });

        return $query;
    }

    /**
     * Apply phone detail filters (condition, device type)
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyPhoneFilters(Builder $query, Request $request): Builder
    {
        $query->whereHas('phoneDetail', function ($q) use ($request) {
            if ($request->filled('condition')) {
                $q->where('condition', $request->condition);
            }

            if ($request->filled('device_type')) {
                $q->where('device_type', $request->device_type);
            }
        });

        return $query;
    }

    /**
     * Apply all standard filters at once
     *
     * @param Builder $query
     * @param Request $request
     * @return Builder
     */
    public function applyAllFilters(Builder $query, Request $request): Builder
    {
        $this->applyContextFilters($query, $request);
        $this->applyPriceFilters($query, $request);
        $this->applySellerFilter($query, $request);
        $this->applyBuyDirectFilter($query, $request);

        return $query;
    }

    /**
     * Get filter parameters from request
     *
     * @param Request $request
     * @return array
     */
    public function getFilterParams(Request $request): array
    {
        return [
            'category' => $request->input('category'),
            'sub_category' => $request->input('sub_category'),
            'brand' => $request->input('brand'),
            'location' => $request->input('location'),
            'min' => $request->input('min'),
            'max' => $request->input('max'),
            'range' => $request->input('range'),
            'seller' => $request->input('seller'),
            'buydirect' => $request->input('buydirect'),
        ];
    }
}
