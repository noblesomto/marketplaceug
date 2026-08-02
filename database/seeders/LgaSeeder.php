<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LgaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('lgas')->truncate();

        // Region ids from StateSeeder: 1=Central, 2=Eastern, 3=Northern, 4=Western
        $data = [
            1 => ['Buikwe', 'Bukomansimbi', 'Butambala', 'Buvuma', 'Gomba', 'Kalangala', 'Kalungu', 'Kampala', 'Kassanda', 'Kayunga', 'Kiboga', 'Kyankwanzi', 'Kyotera', 'Luwero', 'Lwengo', 'Lyantonde', 'Masaka', 'Mityana', 'Mpigi', 'Mubende', 'Mukono', 'Nakaseke', 'Nakasongola', 'Rakai', 'Sembabule', 'Wakiso'],
            2 => ['Amuria', 'Budaka', 'Bududa', 'Bugiri', 'Bugweri', 'Bukedea', 'Bukwa', 'Bulambuli', 'Busia', 'Butaleja', 'Butebo', 'Buyende', 'Iganga', 'Jinja', 'Kaberamaido', 'Kalaki', 'Kamuli', 'Kapchorwa', 'Kapelebyong', 'Kibuku', 'Kumi', 'Kween', 'Luuka', 'Manafwa', 'Mayuge', 'Mbale', 'Namayingo', 'Namisindwa', 'Namutumba', 'Ngora', 'Pallisa', 'Serere', 'Sironko', 'Soroti', 'Tororo'],
            3 => ['Abim', 'Adjumani', 'Agago', 'Alebtong', 'Amolatar', 'Amudat', 'Amuru', 'Apac', 'Arua', 'Dokolo', 'Gulu', 'Kaabong', 'Kitgum', 'Koboko', 'Kole', 'Kotido', 'Kwania', 'Lamwo', 'Lira', 'Madi-Okollo', 'Maracha', 'Moroto', 'Moyo', 'Nabilatuk', 'Nakapiripirit', 'Napak', 'Nebbi', 'Nwoya', 'Obongi', 'Omoro', 'Otuke', 'Oyam', 'Pader', 'Pakwach', 'Terego', 'Yumbe', 'Zombo'],
            4 => ['Buhweju', 'Buliisa', 'Bundibugyo', 'Bunyangabu', 'Bushenyi', 'Hoima', 'Ibanda', 'Isingiro', 'Kabale', 'Kabarole', 'Kagadi', 'Kakumiro', 'Kamwenge', 'Kanungu', 'Kasese', 'Kazo', 'Kibaale', 'Kikuube', 'Kiruhura', 'Kiryandongo', 'Kisoro', 'Kitagwenda', 'Kyegegwa', 'Kyenjojo', 'Mbarara', 'Mitooma', 'Ntoroko', 'Ntungamo', 'Rubanda', 'Rubirizi', 'Rukiga', 'Rukungiri', 'Rwampara', 'Sheema'],
        ];

        foreach ($data as $stateId => $districts) {
            foreach ($districts as $name) {
                DB::table('lgas')->insert([
                    'state_id' => $stateId,
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
