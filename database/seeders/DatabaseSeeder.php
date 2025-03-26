<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Admin',
                'email' => 'admin@email.com',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name' => 'Customer',
                'email' => 'user@email.com',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
        // DB::table('cars')->insert([
        //     [
        //         'name' => 'Civic Turvo',
        //         'type' => 'X4dbY',
        //         'no_plat' => 'AD 5050 YB',
        //         'rent_price' => 200000,
        //         'status' => "available",
        //         'image' => 'https://imgx.gridoto.com/crop/0x0:1200x684/700x465/photo/2021/04/15/5458_3_jpg-20210415095048.jpg',
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        //     [
        //         'name' => 'Xenia Alya',
        //         'type' => 'DBXYZ',
        //         'no_plat' => 'D 7768 TX',
        //         'rent_price' => 500000,
        //         'status' => "available",
        //         'image' => 'https://images.autofun.co.id/file1/80b5c408e4604c46981bcfdae3629895_800.jpeg',
        //         'created_at' => Carbon::now(),
        //         'updated_at' => Carbon::now(),
        //     ],
        // ]);
    }
}