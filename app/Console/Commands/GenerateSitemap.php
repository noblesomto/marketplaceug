<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Blog;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate dynamic sitemap';

    public function handle()
    {
        // Determine the sitemap path based on environment
        if (app()->environment('production')) {
            // Production: save to public_html (one level up from Laravel root)
            $sitemapPath = dirname(base_path()) . '/public_html/core/sys-cache-4a9d82f1.xml';
        } else {
            // Local: save to public directory
            $sitemapPath = public_path('sitemap.xml');
        }

        $sitemap = Sitemap::create();

        // ========== STATIC PAGES ==========
        $staticPages = [
            ['url' => '/', 'priority' => 1.0, 'frequency' => Url::CHANGE_FREQUENCY_DAILY],
            ['url' => '/about-us', 'priority' => 0.8, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/contact-us', 'priority' => 0.8, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/our-terms', 'priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/privacy', 'priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/faq', 'priority' => 0.7, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/how-it-works', 'priority' => 0.9, 'frequency' => Url::CHANGE_FREQUENCY_WEEKLY],
            ['url' => '/career', 'priority' => 0.6, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/privacy-policy', 'priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/cookie-policy', 'priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/billing-policy', 'priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/copyright-policy', 'priority' => 0.5, 'frequency' => Url::CHANGE_FREQUENCY_YEARLY],
            ['url' => '/safety-tips', 'priority' => 0.7, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
            ['url' => '/payments-refunds', 'priority' => 0.6, 'frequency' => Url::CHANGE_FREQUENCY_MONTHLY],
        ];

        foreach ($staticPages as $page) {
            $sitemap->add(
                Url::create($page['url'])
                    ->setLastModificationDate(now())
                    ->setChangeFrequency($page['frequency'])
                    ->setPriority($page['priority'])
            );
        }

        // ========== CATEGORIES ==========
        Category::orderBy('updated_at', 'DESC')->chunk(500, function ($categories) use ($sitemap) {
            foreach ($categories as $category) {
                $sitemap->add(
                    Url::create(rtrim(config('app.url'), '/') . "/category/{$category->category_slug}")
                        ->setLastModificationDate($category->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.9)
                );
            }
        });

        // ========== SUBCATEGORIES ==========
        SubCategory::with('category')
            ->orderBy('updated_at', 'DESC')
            ->chunk(500, function ($subCategories) use ($sitemap) {
                foreach ($subCategories as $subCategory) {
                    $sitemap->add(
                        Url::create(rtrim(config('app.url'), '/') . "/category/{$subCategory->category->category_slug}/{$subCategory->sub_cat_slug}")
                            ->setLastModificationDate($subCategory->updated_at ?? now())
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                }
            });

        // ========== LOCATIONS FROM ADVERTS ==========
        $uniqueStates = Advert::select('state_slug', DB::raw('MAX(updated_at) as updated_at'))
            ->whereNotNull('state_slug')
            ->where('state_slug', '!=', '')
            ->groupBy('state_slug')
            ->get();

        foreach ($uniqueStates as $state) {
            $sitemap->add(
                Url::create("/{$state->state_slug}")
                    ->setLastModificationDate($state->updated_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7)
            );
        }

        // ========== BLOG LISTING PAGE ==========
        $sitemap->add(
            Url::create('/blog')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8)
        );

        // ========== BLOG POSTS ==========
        Blog::where('status', 'published')
            ->orderBy('updated_at', 'DESC')
            ->chunk(500, function ($blogs) use ($sitemap) {
                foreach ($blogs as $blog) {
                    $sitemap->add(
                        Url::create("/blog/{$blog->slug}")
                            ->setLastModificationDate($blog->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.7)
                    );
                }
            });

        // ========== ADVERTS ==========
        Advert::orderBy('updated_at', 'DESC')->chunk(500, function ($adverts) use ($sitemap) {
            foreach ($adverts as $advert) {
                $sitemap->add(
                    Url::create("/{$advert->state_slug}/{$advert->title_slug}/{$advert->ad_id}")
                        ->setLastModificationDate($advert->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                );
            }
        });

        // Save sitemap using the dynamic path
        $sitemap->writeToFile($sitemapPath);

        $this->info("✔ Sitemap generated successfully at: {$sitemapPath}");
    }
}
