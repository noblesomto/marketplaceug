<?php

namespace App\Services;

use App\Models\Advert;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class FeaturedAdPaginator
{
    protected int $perPage = 3;
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

    public function paginate(): LengthAwarePaginator
    {
        $featured = $this->getFeaturedAds();
        $regular = $this->getRegularAds(0); // we no longer need featuredCount in offset logic

        $ads = $this->page === 1 ? $featured->merge($regular) : $regular;

        $total = $this->getRegularAdsTotal(); // purely regular ads

        return new LengthAwarePaginator(
            $ads,
            $total,
            $this->perPage,
            $this->page,
            ['path' => request()->url(), 'query' => request()->query()]
        );
    }


    protected function getFeaturedAds(): Collection
    {
        $query = Advert::with(['firstImage', 'boost' => fn($q) => $q->where('boost_status', 'active')])
            ->where('featured', 'Yes')
            ->activeNotRecentlySold()
            ->whereHas('boost', fn($q) => $q->where('boost_status', 'active'));

        $this->applyFilters($query);

        return $query->inRandomOrder()
            ->limit($this->featuredLimit)
            ->get();
    }

    protected function getRegularAds(int $featuredCount): Collection
    {
        $offset = ($this->page - 1) * $this->perPage;
        $limit = $this->perPage;

        // Regular ads only
        $query = Advert::with('firstImage')
            ->activeNotRecentlySold()
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')->orWhereNull('featured');
            });

        $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }


    protected function getRegularAdsTotal(): int
    {
        $query = Advert::activeNotRecentlySold()
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')->orWhereNull('featured');
            });

        $this->applyFilters($query);

        return $query->count();
    }

    protected function applyFilters($query)
    {
        foreach ($this->filters as $key => $value) {
            if (in_array($key, ['category', 'sub_category', 'brand', 'model', 'state', 'city']) && $value) {
                $query->where($key, $value);
            }
        }
    }
}
