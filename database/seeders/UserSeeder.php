<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // user seeder
        $users = [
            [
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'profile_photo' => null,
                'bio' => 'Fullstack tapi males ngoding!',
                'location' => 'Kota Baru',
            ],
            [
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'profile_photo' => null,
                'bio' => 'Suka nulis di rumah tangga',
                'location' => 'Bandung',
            ],
            [
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'profile_photo' => null,
                'bio' => 'Ngoding, ngoding, ngoding',
                'location' => 'Jambi',
            ],
            [
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'profile_photo' => null,
                'bio' => null,
                'location' => 'Kota Lampung',
            ],
            [
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'profile_photo' => null,
                'bio' => 'Fullstack tapi males ngoding!',
                'location' => 'Kota Baru',
            ],
            [
                'name' => Str::random(10),
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('password'),
                'profile_photo' => null,
                'bio' => 'Fullstack tapi males ngoding!',
                'location' => 'Kota Baru',
            ],
        ];
        foreach ($users as $user) {
            User::create($user);
        }
    }
}
