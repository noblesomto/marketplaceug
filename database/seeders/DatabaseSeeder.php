<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
         $this->call([
             MigrateToSpatiePermissionSeeder::class,
             BoostTypeSeeder::class,
             BoostDurationSeeder::class,
             StateSeeder::class,
             LgaSeeder::class,
             SisterSiteSeeder::class,
         ]);
    }
}
