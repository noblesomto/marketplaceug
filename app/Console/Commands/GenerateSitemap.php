<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Blog;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'Generate sitemap index with child sitemaps';

    private string $baseUrl;
    private string $outputDir;  // filesystem directory to write files into
    private string $publicUrl;  // public-facing URL prefix for sitemap index entries

    public function handle(): void
    {
        $this->baseUrl = rtrim(config('app.url'), '/');

        if (app()->environment('production')) {
            $this->outputDir = dirname(base_path()) . '/public_html/';
            $this->publicUrl = $this->baseUrl;
        } else {
            $this->outputDir = public_path() . '/';
            $this->publicUrl = $this->baseUrl;
        }

        $index = SitemapIndex::create();

        $this->writeStatic($index);
        $this->writeCategories($index);
        $this->writeLocations($index);
        $this->writeBrands($index);
        $this->writeAdverts($index);
        $this->writeBlog($index);

        $indexPath = $this->outputDir . 'sitemap.xml';
        $index->writeToFile($indexPath);

        $this->info("✔ Sitemap index written to: {$indexPath}");
    }

    // -------------------------------------------------------------------------
    // Child sitemap writers
    // -------------------------------------------------------------------------

    private function writeStatic(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();

        $pages = [
            ['/',                  1.0,  Url::CHANGE_FREQUENCY_DAILY],
            ['/about-us',         0.8,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/contact-us',       0.8,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/faq',              0.7,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/how-it-works',     0.9,  Url::CHANGE_FREQUENCY_WEEKLY],
            ['/safety-tips',      0.7,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/career',           0.6,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/our-terms',        0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/privacy',          0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/privacy-policy',   0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/cookie-policy',    0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/billing-policy',   0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/copyright-policy', 0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/payments-refunds', 0.6,  Url::CHANGE_FREQUENCY_MONTHLY],
        ];

        foreach ($pages as [$path, $priority, $freq]) {
            $sitemap->add(
                Url::create($path)
                    ->setLastModificationDate(now())
                    ->setChangeFrequency($freq)
                    ->setPriority($priority)
            );
        }

        $this->write($sitemap, 'sitemap-static.xml', $index);
        $this->info('  → sitemap-static.xml (' . count($pages) . ' URLs)');
    }

    private function writeCategories(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        // Top-level categories
        Category::orderBy('updated_at', 'desc')->chunk(200, function ($categories) use ($sitemap, &$count) {
            foreach ($categories as $cat) {
                $sitemap->add(
                    Url::create("{$this->baseUrl}/category/{$cat->category_slug}")
                        ->setLastModificationDate($cat->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.9)
                );
                $count++;
            }
        });

        // Subcategories
        SubCategory::with('category')->orderBy('updated_at', 'desc')->chunk(500, function ($subcats) use ($sitemap, &$count) {
            foreach ($subcats as $sub) {
                if (!$sub->category) {
                    continue;
                }
                $sitemap->add(
                    Url::create("{$this->baseUrl}/category/{$sub->category->category_slug}/{$sub->sub_cat_slug}")
                        ->setLastModificationDate($sub->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                );
                $count++;
            }
        });

        $this->write($sitemap, 'sitemap-categories.xml', $index);
        $this->info("  → sitemap-categories.xml ({$count} URLs)");
    }

    private function writeLocations(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        // ── State-only pages ──────────────────────────────────────────────────
        DB::table('adverts')
            ->select('state_slug', DB::raw('MAX(updated_at) as last_updated'))
            ->where('ad_status', 1)
            ->whereNotNull('state_slug')
            ->where('state_slug', '!=', '')
            ->groupBy('state_slug')
            ->get()
            ->each(function ($row) use ($sitemap, &$count) {
                $sitemap->add(
                    Url::create("{$this->baseUrl}/{$row->state_slug}")
                        ->setLastModificationDate($this->toDate($row->last_updated))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.8)
                );
                $count++;
            });

        // ── State + Category ──────────────────────────────────────────────────
        DB::table('adverts')
            ->join('categories', 'adverts.category', '=', 'categories.id')
            ->select(
                'adverts.state_slug',
                'categories.category_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->groupBy('adverts.state_slug', 'categories.category_slug')
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->category_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        // ── State + SubCategory ───────────────────────────────────────────────
        DB::table('adverts')
            ->join('sub_categories', 'adverts.sub_category', '=', 'sub_categories.id')
            ->select(
                'adverts.state_slug',
                'sub_categories.sub_cat_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->groupBy('adverts.state_slug', 'sub_categories.sub_cat_slug')
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->sub_cat_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        // ── State + Brand ─────────────────────────────────────────────────────
        DB::table('adverts')
            ->join('brands', 'adverts.brand', '=', 'brands.id')
            ->select(
                'adverts.state_slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->whereNotNull('adverts.brand')
            ->groupBy('adverts.state_slug', 'brands.brand_slug')
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        // ── State + Category + Brand ──────────────────────────────────────────
        // Only include combos with enough inventory to be worth indexing —
        // mirrors the noindex threshold used on the pages themselves.
        DB::table('adverts')
            ->join('categories', 'adverts.category', '=', 'categories.id')
            ->join('brands', 'adverts.brand', '=', 'brands.id')
            ->select(
                'adverts.state_slug',
                'categories.category_slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->whereNotNull('adverts.brand')
            ->groupBy('adverts.state_slug', 'categories.category_slug', 'brands.brand_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->category_slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.6)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-locations.xml', $index);
        $this->info("  → sitemap-locations.xml ({$count} URLs)");
    }

    private function writeBrands(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        // Only index brands that have at least one active advert
        DB::table('brands')
            ->join('adverts', 'brands.id', '=', 'adverts.brand')
            ->select('brands.brand_slug', DB::raw('MAX(adverts.updated_at) as last_updated'))
            ->where('adverts.ad_status', 1)
            ->groupBy('brands.id', 'brands.brand_slug')
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/brand/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-brands.xml', $index);
        $this->info("  → sitemap-brands.xml ({$count} URLs)");
    }

    private function writeAdverts(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        Advert::where('ad_status', 1)
            ->whereNotNull('state_slug')
            ->where('state_slug', '!=', '')
            ->orderBy('updated_at', 'desc')
            ->chunk(500, function ($adverts) use ($sitemap, &$count) {
                foreach ($adverts as $ad) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$ad->state_slug}/{$ad->title_slug}/{$ad->ad_id}")
                            ->setLastModificationDate($ad->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-adverts.xml', $index);
        $this->info("  → sitemap-adverts.xml ({$count} URLs)");
    }

    private function writeBlog(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        $sitemap->add(
            Url::create("{$this->baseUrl}/blog")
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8)
        );
        $count++;

        Blog::where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->chunk(500, function ($blogs) use ($sitemap, &$count) {
                foreach ($blogs as $blog) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/blog/{$blog->slug}")
                            ->setLastModificationDate($blog->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-blog.xml', $index);
        $this->info("  → sitemap-blog.xml ({$count} URLs)");
    }

    // -------------------------------------------------------------------------

    private function write(Sitemap $sitemap, string $filename, SitemapIndex $index): void
    {
        $sitemap->writeToFile($this->outputDir . $filename);

        $index->add(
            SitemapTag::create("{$this->publicUrl}/{$filename}")
                ->setLastModificationDate(now())
        );
    }

    /** Safely cast a raw DB date string or Carbon to DateTimeInterface. */
    private function toDate(mixed $value): \DateTimeInterface
    {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return $value ? \Carbon\Carbon::parse($value) : now();
    }
}
