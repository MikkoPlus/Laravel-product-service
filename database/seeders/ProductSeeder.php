<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $categories = Category::query()->get();

        if ($categories->isEmpty()) {
            $categories = Category::factory()->count(10)->create();
        }

        Product::factory()
            ->count(80)
            ->create([
                'category_id' => fn () => $categories->random()->id,
            ]);
    }
}
