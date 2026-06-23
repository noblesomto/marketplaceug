<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StateSeeder extends Seeder
{
    public function run(): void
    {
        // Explicit IDs + station_ids match the live server exactly.
        // Using upsert so this is safe to re-run on live without changing existing rows.
        $states = [
            ['id' =>  1, 'name' => 'Abia',        'station_id' =>  1,  'slug' => 'abia'],
            ['id' =>  2, 'name' => 'Adamawa',      'station_id' => 61,  'slug' => 'adamawa'],
            ['id' =>  3, 'name' => 'Akwa Ibom',    'station_id' => 33,  'slug' => 'akwa-ibom'],
            ['id' =>  4, 'name' => 'Anambra',      'station_id' => 28,  'slug' => 'anambra'],
            ['id' =>  5, 'name' => 'Bauchi',       'station_id' => 10,  'slug' => 'bauchi'],
            ['id' =>  6, 'name' => 'Bayelsa',      'station_id' => 51,  'slug' => 'bayelsa'],
            ['id' =>  7, 'name' => 'Benue',        'station_id' => 25,  'slug' => 'benue'],
            ['id' =>  8, 'name' => 'Borno',        'station_id' => 24,  'slug' => 'borno'],
            ['id' =>  9, 'name' => 'Cross River',  'station_id' => 11,  'slug' => 'cross-river'],
            ['id' => 10, 'name' => 'Delta',        'station_id' =>  7,  'slug' => 'delta'],
            ['id' => 11, 'name' => 'Ebonyi',       'station_id' => 37,  'slug' => 'ebonyi'],
            ['id' => 12, 'name' => 'Edo',          'station_id' =>  6,  'slug' => 'edo'],
            ['id' => 13, 'name' => 'Ekiti',        'station_id' => 38,  'slug' => 'ekiti'],
            ['id' => 14, 'name' => 'Enugu',        'station_id' => 13,  'slug' => 'enugu'],
            ['id' => 15, 'name' => 'FCT - Abuja',  'station_id' =>  3,  'slug' => 'fct-abuja'],
            ['id' => 16, 'name' => 'Gombe',        'station_id' => 368, 'slug' => 'gombe'],
            ['id' => 17, 'name' => 'Imo',          'station_id' => 29,  'slug' => 'imo'],
            ['id' => 18, 'name' => 'Jigawa',       'station_id' => 55,  'slug' => 'jigawa'],
            ['id' => 19, 'name' => 'Kaduna',       'station_id' => 19,  'slug' => 'kaduna'],
            ['id' => 20, 'name' => 'Kano',         'station_id' => 20,  'slug' => 'kano'],
            ['id' => 21, 'name' => 'Katsina',      'station_id' => 43,  'slug' => 'katsina'],
            ['id' => 22, 'name' => 'Kebbi',        'station_id' => 49,  'slug' => 'kebbi'],
            ['id' => 23, 'name' => 'Kogi',         'station_id' => 23,  'slug' => 'kogi'],
            ['id' => 24, 'name' => 'Kwara',        'station_id' => 17,  'slug' => 'kwara'],
            ['id' => 25, 'name' => 'Lagos',        'station_id' =>  4,  'slug' => 'lagos'],
            ['id' => 26, 'name' => 'Nasarawa',     'station_id' => 44,  'slug' => 'nasarawa'],
            ['id' => 27, 'name' => 'Niger',        'station_id' => 26,  'slug' => 'niger'],
            ['id' => 28, 'name' => 'Ogun',         'station_id' =>  2,  'slug' => 'ogun'],
            ['id' => 29, 'name' => 'Ondo',         'station_id' =>  5,  'slug' => 'ondo'],
            ['id' => 30, 'name' => 'Osun',         'station_id' => 47,  'slug' => 'osun'],
            ['id' => 31, 'name' => 'Oyo',          'station_id' => 14,  'slug' => 'oyo'],
            ['id' => 32, 'name' => 'Plateau',      'station_id' => 18,  'slug' => 'plateau'],
            ['id' => 33, 'name' => 'Rivers',       'station_id' => 30,  'slug' => 'rivers'],
            ['id' => 34, 'name' => 'Sokoto',       'station_id' => 49,  'slug' => 'sokoto'],
            ['id' => 35, 'name' => 'Taraba',       'station_id' => 57,  'slug' => 'taraba'],
            ['id' => 36, 'name' => 'Yobe',         'station_id' => 24,  'slug' => 'yobe'],
            ['id' => 37, 'name' => 'Zamfara',      'station_id' => 62,  'slug' => 'zamfara'],
        ];

        $now = now();
        $rows = array_map(fn($s) => array_merge($s, [
            'created_at' => $now,
            'updated_at' => $now,
        ]), $states);

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('states')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        DB::table('states')->insert($rows);
    }
}
