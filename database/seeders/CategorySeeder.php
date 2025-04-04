<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Category::insert([
            ['name' => 'Kesehatan', 'slug' => 'kesehatan'],
            ['name' => 'Teknologi', 'slug' => 'teknologi'],
            ['name' => 'Politik', 'slug' => 'politik'],
        ]);
    }
}
