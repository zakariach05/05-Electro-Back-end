<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Seller;
use App\Models\Product;

class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $sellers = [
            [
                'name' => 'Electro Zone Casablanca',
                'city' => 'Casablanca',
                'email' => 'contact@electrozone.ma',
                'prep_days' => 1,
            ],
            [
                'name' => 'HighTech Marrakech',
                'city' => 'Marrakech',
                'email' => 'sales@hightechkech.ma',
                'prep_days' => 2,
            ],
            [
                'name' => 'Gaming Shop Tangier',
                'city' => 'Tangier',
                'email' => 'info@gamingshop.ma',
                'prep_days' => 1,
            ]
        ];

        foreach ($sellers as $s) {
            $seller = Seller::create($s);
            
            // Assign this seller to some existing products for testing
            Product::whereNull('seller_id')->inRandomOrder()->take(5)->update([
                'seller_id' => $seller->id,
                'delivery_type' => rand(0, 1) ? 'standard' : 'express'
            ]);
        }

        // Fallback for remaining products
        $firstSeller = Seller::first();
        Product::whereNull('seller_id')->update(['seller_id' => $firstSeller->id]);
    }
}
