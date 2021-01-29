<?php

use Illuminate\Database\Seeder;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $usersData = [
            [
                'name' => 'Nattaya',
                'password' => bcrypt('testuser01'),
                'email' => 'testuser01@gmail.com',
                'image' => 'http://fakeimg.pl/300/',
                'position' => 'Developer',
                'id_card' => '1100800985084',
                'phone' => '66982810499',
                'address' => 'bangkhen',
                'teaching_level' => '1',
                'institution' => 'kmutt',
                'province' => 'bkk',
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'updated_at' => Carbon::now()->format('Y-m-d H:i:s')
            ]
        ];

        DB::table('users')->insert($usersData);
    }
}
