<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing data safely
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        \App\Models\Order::truncate();
        \Illuminate\Support\Facades\DB::table('sub_orders')->delete();
        Product::whereNotNull('id')->delete();
        Category::whereNotNull('id')->delete();
        Seller::whereNotNull('id')->delete();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        $this->call(AdminUserSeeder::class);
        $this->call(LocationSeeder::class);

        // 0. Create Default Seller
        $defaultSeller = Seller::create([
            'name' => 'Electro-05',
            'city' => 'Casablanca',
            'email' => 'contact@electro05.ma',
            'prep_days' => 1
        ]);

        // 1. Create Main Categories (Matching Home.jsx UNIVERS_CATEGORIES)
        $catMap = [
            'Smartphones' => ['slug' => 'smartphones', 'subs' => ['iPhone' => 'iphone', 'Samsung Galaxy' => 'samsung_phone']],
            'PC & Mac' => ['slug' => 'pc-mac', 'subs' => ['MacBook' => 'macbook', 'Laptops' => 'laptops', 'PC Gamer' => 'pc-gamer']],
            'Gaming' => ['slug' => 'gaming', 'subs' => ['PS5' => 'ps5', 'Xbox' => 'xbox', 'Nintendo' => 'nintendo']],
            'Audio Hi-Fi' => ['slug' => 'accessories', 'subs' => ['Casques' => 'headphones', 'Enceintes' => 'speakers']],
            'Électroménager' => ['slug' => 'appliances', 'subs' => ['Café' => 'coffee-machines', 'Cuisinière' => 'kitchen']],
            'Smart Home' => ['slug' => 'smart-home', 'subs' => ['Domotique' => 'domotique', 'Sécurité' => 'security']],
            'TV & Vidéo' => ['slug' => 'tv', 'subs' => ['Smart TV' => 'smart-tv', 'Home Cinéma' => 'home-cinema']],
            'Réseaux' => ['slug' => 'networks', 'subs' => ['Routeurs' => 'routers', 'Wifi' => 'wifi-extenders']]
        ];

        foreach ($catMap as $mainName => $data) {
            $parent = Category::create([
                'name' => $mainName,
                'slug' => $data['slug'],
                'image' => "https://images.unsplash.com/photo-1550009158-9ebf69173e03?q=80&w=800"
            ]);

            foreach ($data['subs'] as $subName => $subSlug) {
                Category::create([
                    'name' => $subName,
                    'slug' => $subSlug,
                    'parent_id' => $parent->id
                ]);
            }
        }

        // 2. Seed Products with professional Unsplash images
        $this->seedProducts();
    }

    private function seedProducts()
    {
        $cats = Category::all()->pluck('id', 'slug')->toArray();

        // --- SMARTPHONES ---
        $this->createProduct($cats['iphone'], 'iPhone 15 Pro Max 256GB Titanium', 'iphone-15-pm', 14500, "https://images.unsplash.com/photo-1696446701796-da61225697cc?q=80&w=800", true, 'neuf', 15);
        $this->createProduct($cats['iphone'], 'iPhone 14 Pro 256GB Deep Purple', 'iphone-14-pro', 10500, "https://images.unsplash.com/photo-1663499482523-1c0c1bae4ce1?q=80&w=800", false, 'neuf', 10);
        $this->createProduct($cats['samsung_phone'], 'Samsung Galaxy S24 Ultra 512GB', 'samsung-s24-ultra', 13500, "https://images.unsplash.com/photo-1610945265064-0e34e5519bbf?q=80&w=800", true, 'neuf', 5);

        // --- TV ---
        $this->createProduct($cats['smart-tv'], 'Sony Bravia XR OLED 55"', 'sony-bravia-55', 18500, "https://images.unsplash.com/photo-1552533231-730ac9f7962c?q=80&w=800", true, 'neuf', 20);
        $this->createProduct($cats['smart-tv'], 'LG C3 OLED 65" 4K Smart TV', 'lg-c3-65', 22500, "https://images.unsplash.com/photo-1509281373149-e957c6296406?q=80&w=800", true, 'neuf', 25);
        $this->createProduct($cats['home-cinema'], 'Samsung QLED 4K 65" Q60C', 'samsung-qled-65', 12000, "https://images.unsplash.com/photo-1593784991095-a20592b739b6?q=80&w=800", true, 'neuf', 18);

        // --- PC & MAC ---
        $this->createProduct($cats['macbook'], 'MacBook Pro 14" M3 Pro 512GB', 'macbook-pro-14', 24900, "https://images.unsplash.com/photo-1517336714467-d23784a1a821?q=80&w=800", true, 'neuf', 5);
        $this->createProduct($cats['laptops'], 'Dell XPS 13 Plus 4K Touch', 'dell-xps-13', 18500, "https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=800", false, 'neuf', 15);
        $this->createProduct($cats['pc-gamer'], 'PC Gamer Master 05 RTX 4080', 'pc-gamer-master', 28500, "https://images.unsplash.com/photo-1587202372775-e229f172b9d7?q=80&w=800", true, 'neuf', 12);

        // --- GAMING ---
        $this->createProduct($cats['ps5'], 'Console PlayStation 5 Slim 1TB', 'ps5-slim', 5500, "https://images.unsplash.com/photo-1606813907291-d86efa9b94db?q=80&w=800", true, 'neuf', 5);
        $this->createProduct($cats['xbox'], 'Console Xbox Series X 1TB', 'xbox-series-x', 6200, "https://images.unsplash.com/photo-1605906302484-ef4b5a3378b1?q=80&w=800", true, 'neuf', 5);

        // --- OTHERS ---
        $this->createProduct($cats['headphones'], 'Sony WH-1000XM5 Noise Cancelling', 'sony-xm5', 3600, "https://images.unsplash.com/photo-1546435770-a3e426bf472b?q=80&w=800", true, 'neuf', 10);
        $this->createProduct($cats['coffee-machines'], 'Nespresso Vertuo Pop - Black', 'nespresso-vertuo', 2100, "https://images.unsplash.com/photo-1510972527921-ce03766a1cf1?q=80&w=800", true, 'neuf', 20);
        $this->createProduct($cats['domotique'], 'Google Nest Hub (2nd Gen)', 'google-nest', 1100, "https://images.unsplash.com/photo-1589492477829-5e65395b66cc?q=80&w=800", false, 'neuf', 15);
        $this->createProduct($cats['routers'], 'TP-Link Archer AXE75 Tri-Band', 'tplink-axe75', 2500, "https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=800", false, 'neuf', 5);
    }

    private function createProduct($catId, $name, $slug, $price, $img, $featured = false, $state = 'neuf', $promo = null, $desc = null)
    {
        Product::create([
            'category_id' => $catId,
            'seller_id' => Seller::first()->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $desc ?? "Produit premium sélectionné par Electro-05 pour sa qualité et ses performances exceptionnelles.",
            'price' => $price,
            'old_price' => $promo ? round($price / (1 - $promo/100)) : null,
            'image' => $img,
            'stock' => rand(10, 100),
            'is_featured' => $featured,
            'state' => $state,
            'promo' => $promo
        ]);
    }
}
