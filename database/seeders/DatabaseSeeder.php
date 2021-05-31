<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        DB::table('units')->insert([
            'name' => 'APT 11',
            'id_owner' => 1,
        ]);
        DB::table('units')->insert([
            'name' => 'APT 12',
            'id_owner' => 1,
        ]);
        DB::table('units')->insert([
            'name' => 'APT 13',
        ]);
        DB::table('units')->insert([
            'name' => 'APT 14',
        ]);

        DB::table('areas')->insert([
            'allowed' => 1,
            'title' => 'Academia',
            'cover' => 'gym.jpg',
            'days' => '1,2,4,5',
            'start_time' => '06:00:00',
            'end_time' => '22:00:00',
        ]);
        DB::table('areas')->insert([
            'allowed' => 1,
            'title' => 'Piscina',
            'cover' => 'pool.jpg',
            'days' => '1,2,3,4,5',
            'start_time' => '07:00:00',
            'end_time' => '23:00:00',
        ]);
        DB::table('areas')->insert([
            'allowed' => 1,
            'title' => 'Churrasqueira',
            'cover' => 'barbecue.jpg',
            'days' => '4,5,6',
            'start_time' => '09:00:00',
            'end_time' => '22:00:00',
        ]);

        DB::table('walls')->insert([
            'title' => 'Título de aviso de teste',
            'body' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            'date_created' => '2021-05-29 15:00:00',
        ]);
        DB::table('walls')->insert([
            'title' => 'Alerta geral',
            'body' => 'Lorem Ipsum has been the industry standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.',
            'date_created' => '2021-05-30 18:00:00',
        ]);
    }
}
