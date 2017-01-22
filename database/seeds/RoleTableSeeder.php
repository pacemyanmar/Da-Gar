<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            [
                'level' => '9',
                'group' => 'admin',
                'role_name' => 'super_admin',
                'description' => 'Super Admin',
                'created_at' => Carbon::now('Asia/Rangoon'),
                'updated_at' => Carbon::now('Asia/Rangoon'),
            ],
            [
                'level' => '8',
                'group' => 'admin',
                'role_name' => 'admin',
                'description' => 'Admin',
                'created_at' => Carbon::now('Asia/Rangoon'),
                'updated_at' => Carbon::now('Asia/Rangoon'),
            ],
            [
                'level' => '7',
                'group' => 'supervisor',
                'role_name' => 'supervisor',
                'description' => 'Supervisor',
                'created_at' => Carbon::now('Asia/Rangoon'),
                'updated_at' => Carbon::now('Asia/Rangoon'),
            ],
            [
                'level' => '4',
                'group' => 'data',
                'role_name' => 'doublechecker',
                'description' => 'Double Checker',
                'created_at' => Carbon::now('Asia/Rangoon'),
                'updated_at' => Carbon::now('Asia/Rangoon'),
            ],
            [
                'level' => '4',
                'group' => 'data',
                'role_name' => 'entryclerk',
                'description' => 'Data Entry Clerk',
                'created_at' => Carbon::now('Asia/Rangoon'),
                'updated_at' => Carbon::now('Asia/Rangoon'),
            ],
            [
                'level' => '0',
                'group' => 'guest',
                'role_name' => 'guest',
                'description' => 'Guest',
                'created_at' => Carbon::now('Asia/Rangoon'),
                'updated_at' => Carbon::now('Asia/Rangoon'),
            ],

        ];
        DB::table('roles')->insert($roles);
    }
}
