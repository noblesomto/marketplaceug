<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;
use App\Models\Advert;

class GenerateSitemap extends Command
{
    protected $signature = 'sitemap:generate';
    protected $description = 'Generate dynamic sitemap';

    public function handle()
    {
        $sitemap = Sitemap::create();

        // Home
        $sitemap->add(
            Url::create('/')
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                ->setPriority(1.0)
        );

        // Dynamic adverts
        Advert::chunk(500, function ($adverts) use ($sitemap) {
            foreach ($adverts as $advert) {
                $sitemap->add(
                    Url::create("{$advert->state_slug}/{$advert->title_slug}/{$advert->ad_id}")
                        ->setLastModificationDate($advert->updated_at)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.8)
                );
            }
        });

        // Save sitemap
        $sitemap->writeToFile(public_path('sitemap.xml'));

        $this->info('✔ Sitemap generated successfully.');
    }
}
