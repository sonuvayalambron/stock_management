<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'Dell XPS 15 Laptop',
                'sku' => 'LAPTOP-DELL-XPS15',
                'is_active' => true,
            ],
            [
                'name' => 'iPhone 14 Pro Max 256GB',
                'sku' => 'PHONE-IPHONE-14PM-256',
                'is_active' => true,
            ],
            [
                'name' => 'Samsung Galaxy S23 Ultra',
                'sku' => 'PHONE-SAMSUNG-S23U',
                'is_active' => true,
            ],
            [
                'name' => 'MacBook Pro 16" M2',
                'sku' => 'LAPTOP-APPLE-MBP16-M2',
                'is_active' => true,
            ],
            [
                'name' => 'iPad Air 5th Gen',
                'sku' => 'TABLET-APPLE-IPADAIR5',
                'is_active' => true,
            ],
            [
                'name' => 'Sony WH-1000XM5 Headphones',
                'sku' => 'AUDIO-SONY-WH1000XM5',
                'is_active' => true,
            ],
            [
                'name' => 'Logitech MX Master 3S Mouse',
                'sku' => 'ACCESSORY-LOGITECH-MXM3S',
                'is_active' => true,
            ],
            [
                'name' => 'Samsung 27" 4K Monitor',
                'sku' => 'MONITOR-SAMSUNG-27-4K',
                'is_active' => true,
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}