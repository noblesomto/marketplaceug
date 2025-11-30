<?php
namespace App\Console\Commands;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Advert;
use App\Models\Blog;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate dynamic sitemap';

    public function handle()
    {
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

        // ========== BLOG POSTS ==========
        Blog::where('status', 'published') // Only published blogs
            ->orderBy('updated_at', 'DESC')
            ->chunk(500, function ($blogs) use ($sitemap) {
                foreach ($blogs as $blog) {
                    $sitemap->add(
                        Url::create("/blog/{$blog->slug}") // Adjust URL structure as needed
                            ->setLastModificationDate($blog->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.7)
                    );
                }
            });

        // Blog category/listing page (if you have one)
        $sitemap->add(
            Url::create('/blog')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8)
        );

        // ========== ADVERTS ==========
        Advert::orderBy('updated_at', 'DESC')->chunk(500, function ($adverts) use ($sitemap) {
            foreach ($adverts as $advert) {
                $sitemap->add(
                    Url::create("{$advert->state_slug}/{$advert->title_slug}/{$advert->ad_id}")
                        ->setLastModificationDate($advert->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                );
            }
        });

        // Save to public_html
        $sitemap->writeToFile(dirname(base_path()) . '/public_html/sitemap.xml');

        $this->info('✔ Sitemap generated successfully with blogs and static pages.');
    }
}
