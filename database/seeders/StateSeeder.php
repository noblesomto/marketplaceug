<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $regions = ['Central', 'Eastern', 'Northern', 'Western'];

        foreach ($regions as $index => $name) {
            DB::table('states')->insert([
                'id' => $index + 1,
                'name' => $name,
                'station_id' => 0,
                'slug' => Str::slug($name),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
