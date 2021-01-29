<?php

use Illuminate\Database\Seeder;

class BadgesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {

        $badgesData = [
            [
                'course_id' => 1,
                'name' => 'badge_example',
                'image' => '/students/badges/badge_example.png',
                'xp' => 1
            ]
        ];

        DB::table('badges')->insert($badgesData);
    }
}
