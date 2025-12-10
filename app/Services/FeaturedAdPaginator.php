<?php
namespace App\Services;

use App\Models\Advert;
use Illuminate\Support\Collection;

class FeaturedAdPaginator
{
    protected int $perPage = 20;
    protected int $featuredLimit = 6;
    protected array $filters = [];

    public function __construct(protected int $page = 1)
    {
        $this->page = max(1, $page);
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function get(): array
    {
        $featured = collect();

        if ($this->page === 1) {
            $featured = $this->getFeaturedAds();
        }

        $regular = $this->getRegularAds();
        $ads = $this->page === 1 ? $featured->merge($regular) : $regular;

        $hasMore = $this->hasMoreAds();

        return [
            'ads' => $ads,
            'hasMore' => $hasMore,
            'nextPage' => $this->page + 1
        ];
    }

    protected function getFeaturedAds(): Collection
    {
        $query = Advert::with(['firstImage', 'car', 'brands', 'owner', 'boost' => fn($q) => $q->where('boost_status', 'active')])
            ->where('featured', 'Yes')
            ->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->whereHas('boost', fn($q) => $q->where('boost_status', 'active'));

        $this->applyFilters($query);

        return $query->inRandomOrder()
            ->limit($this->featuredLimit)
            ->get();
    }

    protected function getRegularAds(): Collection
    {
        $offset = ($this->page - 1) * $this->perPage;
        $limit = $this->perPage;

        $query = Advert::with('firstImage', 'car', 'brands', 'owner')
            ->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')->orWhereNull('featured');
            });

        $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    protected function hasMoreAds(): bool
    {
        $offset = $this->page * $this->perPage;

        $query = Advert::where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')->orWhereNull('featured');
            });

        $this->applyFilters($query);

        $totalCount = $query->count();
        return $totalCount > $offset;
    }

    protected function applyFilters($query)
    {
        foreach ($this->filters as $key => $value) {
            // Add 'state_slug' to the allowed filters
            if (in_array($key, ['category', 'sub_category', 'brand', 'model', 'state', 'state_slug', 'city']) && $value) {
                $query->where($key, $value);
            }
        }
    }
}
