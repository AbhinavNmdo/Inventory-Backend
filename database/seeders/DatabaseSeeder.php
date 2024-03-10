<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'Abhinav Namdeo',
            'username' => 'abhaynam22',
            'password' => bcrypt('Admin@123'),
            'role' => 'Admin'
        ]);

        \App\Models\Category::create([
            'name' => 'Stationary',
            'created_by' => 1,
        ]);

        \App\Models\Category::create([
            'name' => 'Electronics',
            'created_by' => 1
        ]);

        \App\Models\SubCategory::create([
            'category_id' => 1,
            'name' => 'Pen',
            'created_by' => 1
        ]);

        \App\Models\SubCategory::create([
            'category_id' => 1,
            'name' => 'Pencil',
            'created_by' => 1
        ]);

        \App\Models\SubCategory::create([
            'category_id' => 2,
            'name' => 'Monitor',
            'created_by' => 1
        ]);

        \App\Models\SubCategory::create([
            'category_id' => 2,
            'name' => 'Keyboard',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 2,
            'name' => 'Doms Pencil',
            'stock' => '0.00',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 1,
            'name' => 'Trimax Pen',
            'stock' => '0.00',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 4,
            'name' => 'Evo Fox Membrane keyboard',
            'stock' => '0.00',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 4,
            'name' => 'Cosmic byte mechenical keyboard',
            'stock' => '0.00',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 3,
            'name' => 'Lenovo 19 inch keyboard',
            'stock' => '0.00',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 3,
            'name' => 'HP 21 inch Monitor',
            'stock' => '0.00',
            'created_by' => 1
        ]);

        \App\Models\Product::create([
            'sub_category_id' => 2,
            'name' => 'Natraj Pencil',
            'stock' => '0.00',
            'created_by' => 1
        ]);
    }
}
