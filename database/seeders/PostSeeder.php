<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::factory(10)->create();
        // post seeder

        $posts = [
            [
                'title' => 'Menjadi manusia morning person di bulan ramadhan',
                'content' => 'lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua bla bala bla baal',
                'image' => null,
                'category_id' => 1
            ],
            [
                'title' => 'Pengaruh teknologi terhadap kesehatan rumah tangga',
                'content' => 'Teknologi lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua bla bala bla baal',
                'image' => null,
                'category_id' => 2
            ],
            [
                'title' => 'Pengaruh politik terhadap kemajuan sebuah negara berkembang',
                'content' => 'Politik lorem ipsum dolor sit amet consectetur adipiscing elit sed do eiusmod tempor incididunt ut labore et dolore magna aliqua bla bala bla baal',
                'image' => null,
                'category_id' => 3
            ]
        ];

        foreach ($posts as $post) {
            $post['user_id'] = $user->random()->id;
            Post::create($post);
        }
    }
}
