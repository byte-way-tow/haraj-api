<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Cars',
                'name_ar' => 'السيارات',
                'description' => 'New and used cars for sale',
                'icon' => 'car',
                'sort_order' => 1,
            ],
            [
                'name' => 'Real Estate',
                'name_ar' => 'العقارات',
                'description' => 'Houses, apartments, and land for sale or rent',
                'icon' => 'home',
                'sort_order' => 2,
            ],
            [
                'name' => 'Electronics',
                'name_ar' => 'الإلكترونيات',
                'description' => 'Phones, computers, and electronic devices',
                'icon' => 'smartphone',
                'sort_order' => 3,
            ],
            [
                'name' => 'Furniture',
                'name_ar' => 'الأثاث',
                'description' => 'Home and office furniture',
                'icon' => 'sofa',
                'sort_order' => 4,
            ],
            [
                'name' => 'Clothing',
                'name_ar' => 'الملابس',
                'description' => 'Men, women, and children clothing',
                'icon' => 'shirt',
                'sort_order' => 5,
            ],
            [
                'name' => 'Services',
                'name_ar' => 'الخدمات',
                'description' => 'Professional and personal services',
                'icon' => 'briefcase',
                'sort_order' => 6,
            ],
            [
                'name' => 'Jobs',
                'name_ar' => 'الوظائف',
                'description' => 'Job opportunities and career listings',
                'icon' => 'users',
                'sort_order' => 7,
            ],
            [
                'name' => 'Animals',
                'name_ar' => 'الحيوانات',
                'description' => 'Pets and animals for sale',
                'icon' => 'heart',
                'sort_order' => 8,
            ],
            [
                'name' => 'Sports',
                'name_ar' => 'الرياضة',
                'description' => 'Sports equipment and accessories',
                'icon' => 'activity',
                'sort_order' => 9,
            ],
            [
                'name' => 'Books',
                'name_ar' => 'الكتب',
                'description' => 'Books, magazines, and educational materials',
                'icon' => 'book',
                'sort_order' => 10,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}

