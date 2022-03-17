<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $date = $faker->dateTimeThisMonth($max = 'now');
        DB::table('users')->insert([
            'name' => 'Sithu Thwin',
            'username' => 'herzcthu',
            'email' => 'sithu@thwin.net',
            'role_id' => 1,
            'password' => bcrypt('noghFuAnReogInca'),
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
