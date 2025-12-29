<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data
        Product::whereNotNull('id')->delete();
        Category::whereNotNull('id')->delete();
        $this->call(AdminUserSeeder::class);
        $this->call(LocationSeeder::class);

        // Use explicit 127.0.0.1:8000 to ensure images load during development
        $baseUrl = 'http://127.0.0.1:8000/storage/Produit';

        // 1. Create Categories
        $categories = [
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                // Use a real image for the category cover
                'image' => "$baseUrl/Phone/Apple/IPhone-15/IPhone-15-pro-max/IPhone 15 pro max 256go Titane Natural.png",
                'folder' => 'Phone',
                'children' => [
                    ['name' => 'iPhone', 'slug' => 'iphone', 'brand_folder' => 'Apple'],
                    ['name' => 'Samsung', 'slug' => 'samsung', 'brand_folder' => 'Samsung'],
                    ['name' => 'Xiaomi', 'slug' => 'xiaomi', 'brand_folder' => 'Xiaomi'],
                    ['name' => 'Oppo', 'slug' => 'oppo', 'brand_folder' => 'Oppo'],
                ]
            ],
            [
                'name' => 'PC & Mac',
                'slug' => 'pc-mac',
                'image' => "$baseUrl/Pc/cover.png",
                'folder' => 'Pc',
                'children' => [
                    ['name' => 'MacBook Pro', 'slug' => 'macbook-pro', 'brand_folder' => 'PC portable/Apple (MacBook)'],
                    ['name' => 'MacBook Air', 'slug' => 'macbook-air', 'brand_folder' => 'PC portable/Apple (MacBook)'],
                    ['name' => 'PC Gamer', 'slug' => 'pc-gamer', 'brand_folder' => 'Pc Gamer'],
                    ['name' => 'Bureautique', 'slug' => 'office', 'brand_folder' => 'PC portable/Dell'], // Example mapping
                ]
            ],
            [
                'name' => 'Gaming',
                'slug' => 'gaming',
                'image' => "$baseUrl/Jeux & Consoles/cover.png",
                'folder' => 'Jeux & Consoles',
                'children' => [
                    ['name' => 'PlayStation 5', 'slug' => 'ps5', 'brand_folder' => 'Sony'],
                    ['name' => 'Xbox Series X', 'slug' => 'xbox', 'brand_folder' => 'Microsoft'],
                    ['name' => 'Nintendo Switch', 'slug' => 'nintendo', 'brand_folder' => 'Nintendo'],
                ]
            ],
            [
                'name' => 'Accessoires',
                'slug' => 'accessories',
                'image' => "$baseUrl/Audio  Hi-Fi  Casques/cover.png",
                'folder' => 'Audio Hi-Fi Casques',
                'children' => [
                    ['name' => 'Audio', 'slug' => 'audio', 'brand_folder' => 'JBL'], // Example
                    ['name' => 'Montres Connectées', 'slug' => 'smartwatches', 'brand_folder' => 'Apple'],
                ]
            ],
        ];

        foreach ($categories as $catData) {
            $parent = Category::create([
                'name' => $catData['name'],
                'slug' => $catData['slug'],
                'image' => $catData['image']
            ]);

            foreach ($catData['children'] as $childData) {
                $child = Category::create([
                    'name' => $childData['name'],
                    'slug' => $childData['slug'],
                    'parent_id' => $parent->id,
                ]);

                // Helper to build path: /storage/Produit/{CategoryFolder}/{BrandSubFolder}/{slug}.jpg
                $folder = $catData['folder'];
                $subFolder = $childData['brand_folder'] ?? 'Autres';
                
                // Seed Products
                if ($child->slug === 'iphone') {
                    $applePath = "$baseUrl/$folder/$subFolder";
                    
                    // --- iPhone 13 Series ---
                    $this->createProduct($child->id, 'Apple iPhone 13 Pro 256GB Silver', 'iphone-13-pro-silver', 7900, "$applePath/IPhone-13/IPhone-13-pro/Apple_iPhone-13 Pro 256GB.png", false, 'occasion');
                    $this->createProduct($child->id, 'Apple iPhone 13 Pro 256GB Gold', 'iphone-13-pro-gold', 8200, "$applePath/IPhone-13/IPhone-13-pro/apple-iphone-13-pro-256GB-gold-cpo.png", false, 'reconditionné');
                    $this->createProduct($child->id, 'Apple iPhone 13 Pro 256GB Alpine Green', 'iphone-13-pro-green', 8200, "$applePath/IPhone-13/IPhone-13-pro/iphone13 pro 256GB-green.png");
                    
                    $this->createProduct($child->id, 'Apple iPhone 13 Pro Max 256GB Sierra Blue', 'iphone-13-pro-max-blue', 9000, "$applePath/IPhone-13/IPhone-13-pro-max/IPhone 13-Pro-max 256GB Bleu.png", false, 'reconditionné');
                    $this->createProduct($child->id, 'Apple iPhone 13 Pro Max 256GB Gold', 'iphone-13-pro-max-gold', 9200, "$applePath/IPhone-13/IPhone-13-pro-max/IPhone 13-Pro-max 256GB Gold.png");
                    $this->createProduct($child->id, 'Apple iPhone 13 Pro Max 256GB Silver', 'iphone-13-pro-max-white', 9000, "$applePath/IPhone-13/IPhone-13-pro-max/IPhone 13-Pro-max 256GB white.png");

                    // --- iPhone 14 Series ---
                    $this->createProduct($child->id, 'Apple iPhone 14 Pro 256GB Gold', 'iphone-14-pro-gold', 10500, "$applePath/IPhone-14-/IPhone-14-pro/IPhone 14  pro 256GB Gold.png", false, 'neuf');
                    $this->createProduct($child->id, 'Apple iPhone 14 Pro 256GB Deep Purple', 'iphone-14-pro-purple', 10500, "$applePath/IPhone-14-/IPhone-14-pro/IPhone 14  pro 256GB Violet Intense.png");
                    
                    $this->createProduct($child->id, 'iPhone 14 Pro Max - Space Edition', 'iphone-14-pro-max-black', 11900, "$applePath/IPhone-14-/IPhone-14-pro-max/IPhone 14  pro max 256GB Black.png", true, 'neuf', 5, "L'élégance du noir sidéral alliée à la puissance de la puce A16 Bionic.");
                    $this->createProduct($child->id, 'iPhone 14 Pro Max - Gold Luxury', 'iphone-14-pro-max-gold', 11900, "$applePath/IPhone-14-/IPhone-14-pro-max/IPhone 14  pro max 256GB Gold.png");
                    $this->createProduct($child->id, 'iPhone 14 Pro Max - Silver Arctic', 'iphone-14-pro-max-silver', 11900, "$applePath/IPhone-14-/IPhone-14-pro-max/IPhone 14 pro et pro max 256GB White.png");


                    // --- iPhone 15 Series ---
                    $this->createProduct($child->id, 'Apple iPhone 15 Pro Max 256GB White Titanium', 'iphone-15-pro-max-white', 14500, "$applePath/IPhone-15/IPhone-15-pro-max/IPhone 15 pro max 1TB White Titanium.png", true, 'neuf');
                    $this->createProduct($child->id, 'iPhone 15 Pro Max - Blue Titanium', 'iphone-15-pro-max-blue', 13900, "$applePath/IPhone-15/IPhone-15-pro-max/IPhone 15 pro max 256go Blue Titanium.png", true, 'neuf', 8, "Le titane de qualité aérospatiale rencontre la puissance brute.");
                    $this->createProduct($child->id, 'iPhone 15 Pro Max - Natural Titanium', 'iphone-15-pro-max-natural', 14200, "$applePath/IPhone-15/IPhone-15-pro-max/IPhone 15 pro max 256go Titane Natural.png", true, 'neuf', null, "La pureté du titane naturel pour un look sophistiqué.");
                    $this->createProduct($child->id, 'iPhone 15 Pro Max - Black Stealth', 'iphone-15-pro-max-black', 14200, "$applePath/IPhone-15/IPhone-15-pro-max/iPhone_15_Pro_Max_Black_Titanium 1TB.png", true, 'neuf');

                    // --- iPhone 16 Series ---
                    $this->createProduct($child->id, 'Apple iPhone 16 256GB Black', 'iphone-16-black', 10900, "$applePath/IPhone-16/IPhone 16 Normal/IPhone 16 Normal Black 256GB .png", true, 'neuf');
                    $this->createProduct($child->id, 'Apple iPhone 16 256GB Blue', 'iphone-16-blue', 10900, "$applePath/IPhone-16/IPhone 16 Normal/IPhone 16 Normal blue 256GB.png");
                    $this->createProduct($child->id, 'Apple iPhone 16 256GB Green', 'iphone-16-green', 10900, "$applePath/IPhone-16/IPhone 16 Normal/IPhone 16 Normal green 256GB.png");
                    
                    $this->createProduct($child->id, 'iPhone 16 Pro Max - Future Titanium', 'iphone-16-pro-max-natural', 16900, "$applePath/IPhone-16/IPhone-16-pro-max/IPhone 16 pro max 256go Titane Natural.png", true, 'neuf', null, "L'intelligence Apple au service de votre créativité.");

                    // --- iPhone 17 Series ---
                    $this->createProduct($child->id, 'Apple iPhone 17 256GB Pink', 'iphone-17-pink', 11500, "$applePath/IPhone-17/IPhone 17 Normal/iPhone-17 Pink 256GB.png", true, 'neuf');
                    $this->createProduct($child->id, 'Apple iPhone 17 256GB Pastel Blue', 'iphone-17-blue', 11500, "$applePath/IPhone-17/IPhone 17 Normal/iphone 17 Bleu Pastel. 256Gb.png");
                    
                    $this->createProduct($child->id, 'Apple iPhone 17 Pro Max 1TB Deep Blue', 'iphone-17-pro-max-deep-blue', 19500, "$applePath/IPhone-17/IPhone-17-pro-max/IPhone 17pro-max 1TB deep blue.png", true, 'neuf');
                    $this->createProduct($child->id, 'Apple iPhone 17 Pro Max 1TB Cosmic Orange', 'iphone-17-pro-max-orange', 19500, "$applePath/IPhone-17/IPhone-17-pro-max/iPhone-17-Pro-1TB cosmic-orange.png", true, 'neuf');
                    $this->createProduct($child->id, 'Apple iPhone 17 Pro Max 1TB Pearl White', 'iphone-17-pro-max-white', 19500, "$applePath/IPhone-17/IPhone-17-pro-max/iphone-17-pro-max-1TB white.png", true, 'neuf');
                }
            }
        }
    }

    private function createProduct($catId, $name, $slug, $price, $img, $featured = false, $state = 'neuf', $promo = null, $desc = null)
    {
        Product::create([
            'category_id' => $catId,
            'name' => $name,
            'slug' => $slug,
            'description' => $desc ?? "Découvrez l'innovation et la performance avec " . $name . ". Un produit premium conçu pour répondre à toutes vos exigences technologiques.",
            'price' => $price,
            'old_price' => $promo ? $price / (1 - $promo/100) : null,
            'image' => $img,
            'stock' => rand(5, 50),
            'is_featured' => $featured,
            'state' => $state,
            'promo' => $promo
        ]);
    }
}
