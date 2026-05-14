<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'Electronics',
            'Clothing',
            'Home & Garden',
            'Sports',
            'Books',
        ];

        foreach ($defaults as $name) {
            Category::query()->firstOrCreate(['name' => $name]);
        }

        Category::factory()->count(15)->create();
    }
}
