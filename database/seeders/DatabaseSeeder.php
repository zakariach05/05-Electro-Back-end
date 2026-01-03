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
        // 1. Create Main Categories (Matching Home.jsx UNIVERS_CATEGORIES & FilterSidebar)
        $catMap = [
            'Smartphones' => [
                'slug' => 'smartphones', 
                'image' => 'https://images.unsplash.com/photo-1511707171634-5f897ff02aa9?q=80&w=800',
                'subs' => ['iPhone' => 'iphone', 'Samsung Galaxy' => 'samsung_phone', 'Xiaomi' => 'xiaomi', 'Oppo' => 'oppo']
            ],
            'PC & Mac' => [
                'slug' => 'pc-mac', 
                'image' => 'https://images.unsplash.com/photo-1517336714467-d23784a1a821?q=80&w=800',
                'subs' => ['MacBook' => 'macbook', 'Laptops' => 'laptops', 'PC Gamer' => 'pc-gamer', 'Bureautique' => 'office-pc']
            ],
            'Gaming' => [
                'slug' => 'gaming', 
                'image' => 'https://images.unsplash.com/photo-1605899435973-ca2d1a8861cf?q=80&w=800',
                'subs' => ['PS5' => 'ps5', 'Xbox' => 'xbox', 'Nintendo' => 'nintendo', 'Accessoires Gaming' => 'gaming-accessories']
            ],
            'TV & Son' => [
                'slug' => 'tv-son', 
                'image' => 'https://images.unsplash.com/photo-1593359677879-a4bb92f829d1?q=80&w=800',
                'subs' => ['Smart TV' => 'smart-tv', 'Home Cinéma' => 'home-cinema', 'Barres de son' => 'soundbars', 'Projecteurs' => 'projectors']
            ],
            'Tablettes' => [
                'slug' => 'tablettes', 
                'image' => 'https://images.unsplash.com/photo-1544244015-0df4b3ffc6b0?q=80&w=800',
                'subs' => ['iPad' => 'ipad', 'Samsung Tab' => 'samsung-tab', 'Lenovo' => 'lenovo-tab', 'E-readers' => 'ereaders']
            ],
            'Accessoires' => [
                'slug' => 'accessoires', 
                'image' => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?q=80&w=800',
                'subs' => ['Casques' => 'headphones', 'Enceintes' => 'speakers', 'Câbles' => 'cables', 'Chargeurs' => 'chargers', 'Powerbanks' => 'powerbanks']
            ],
            'Domotique' => [
                'slug' => 'domotique', 
                'image' => 'https://images.unsplash.com/photo-1558002038-1055907df827?q=80&w=800',
                'subs' => ['Éclairage' => 'lighting', 'Sécurité' => 'security', 'Assistants Vocaux' => 'voice-assistants', 'Caméras' => 'cameras']
            ],
            'Électroménager' => [
                'slug' => 'electromenager', 
                'image' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?q=80&w=800',
                'subs' => ['Café' => 'coffee-machines', 'Cuisinière' => 'kitchen', 'Aspirateurs' => 'vacuums', 'Traitement de l\'air' => 'air-treatment']
            ],
            'Réseaux' => [
                'slug' => 'reseaux', 
                'image' => 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=800',
                'subs' => ['Routeurs' => 'routers', 'Wifi' => 'wifi-extenders', 'Switch' => 'switches', 'CPL' => 'cpl']
            ]
        ];

        foreach ($catMap as $mainName => $data) {
            $parent = Category::create([
                'name' => $mainName,
                'slug' => $data['slug'],
                'image' => $data['image']
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

        // 3. Seed Orders (Today & History)
        $this->call(OrderSeeder::class);
    }

    private function seedProducts()
    {
        $cats = Category::all()->pluck('id', 'slug')->toArray();

        // --- SMARTPHONES ---
        $this->createProduct($cats['iphone'], 'iPhone 15 Pro Max 256GB Titanium', 'iphone-15-pm', 14500, "Produit/Phone/Apple/IPhone-15/IPhone-15-pro-max/IPhone 15 pro max 256go Titane Natural.png", true, 'neuf', 15);
        $this->createProduct($cats['iphone'], 'iPhone 14 Pro 256GB Deep Purple', 'iphone-14-pro', 10500, "Produit/Phone/Apple/IPhone-15/IPhone-15-pro-max/IPhone 15 pro max 256go Blue Titanium.png", false, 'neuf', 10);
        $this->createProduct($cats['samsung_phone'], 'Samsung Galaxy S24 Ultra 512GB', 'samsung-s24-ultra', 13500, "Produit/Phone/Samsung/samsung-galaxy-s24-ultra-12go_256go-noir-titane.png", true, 'neuf', 5);

        // --- TV ---
        $this->createProduct($cats['smart-tv'], 'Sony Bravia XR OLED 55"', 'sony-bravia-55', 18500, "Produit/Tv/SONY KDL-50W656A - 126 cm .png", true, 'neuf', 20);
        $this->createProduct($cats['smart-tv'], 'LG C3 OLED 60" 4K Smart TV', 'lg-c3-65', 22500, "Produit/Tv/Smart TV LED 60 LG ThinQ AI 4K HDR .png", true, 'neuf', 25);
        $this->createProduct($cats['home-cinema'], 'Samsung QLED 4K 60" Q60C', 'samsung-qled-65', 12000, "Produit/Tv/samsung-tv-bu8000-smart-tv-crystal-uhd-60-pouces.png", true, 'neuf', 18);

        // --- PC & MAC ---
        $this->createProduct($cats['macbook'], 'MacBook Air 13" M1', 'macbook-air-m1', 9900, "Produit/Pc/PC portable/Apple (MacBook)/Apple-MacBook-Air-Retina-13-inch-2020-tera.png", true, 'neuf', 5);
        $this->createProduct($cats['laptops'], 'Dell XPS 13 Plus 4K Touch', 'dell-xps-13', 18500, "Produit/Pc/PC portable/Apple (MacBook)/Apple-iMac-21.5-pouces-avec-ecran-Retina-4K.png", false, 'neuf', 15); // Fallback image as Dell not found
        $this->createProduct($cats['pc-gamer'], 'PC Gamer Nexus Ryzen 7 RTX 4060', 'pc-gamer-nexus', 15500, "Produit/Pc/Pc Gamer/pc-gamer-nexus-ryzen7-rtx4060.png", true, 'neuf', 12);

        // --- GAMING ---
        $this->createProduct($cats['ps5'], 'Console PlayStation 5 Pro', 'ps5-pro', 8500, "Produit/Jeux & Consoles/PS5-Pro-1.png", true, 'neuf', 5);
        $this->createProduct($cats['xbox'], 'Console Xbox Series X 1TB', 'xbox-series-x', 6200, "Produit/Jeux & Consoles/Sony-PlayStation-5-PS5-Gaming-Console-Disc-Version.avif", true, 'neuf', 5); // Using PS5 as placeholder or generic

        // --- OTHERS ---
        $this->createProduct($cats['headphones'], 'Apple AirPods Max', 'airpods-max', 6500, "Produit/Audio  Hi-Fi  Casques/Casques sans fil (Bluetooth) & filaires/casque-airpods-max-apple-sans-fil-high-quality.png", true, 'neuf', 10);
        $this->createProduct($cats['coffee-machines'], 'Nespresso Vertuo Pop - Black', 'nespresso-vertuo', 2100, "Produit/phone/Samsung/Samsung Galaxy S23 Ultra.png", true, 'neuf', 20); // Placeholder until appliance img found
        $this->createProduct($cats['voice-assistants'], 'Google Nest Hub (2nd Gen)', 'google-nest', 1100, "Produit/phone/Samsung/samsung-galaxy-a16-4go-128go-noir-maroc.png", false, 'neuf', 15); // Placeholder
        $this->createProduct($cats['routers'], 'TP-Link Archer AXE75 Tri-Band', 'tplink-axe75', 2500, "Produit/phone/Samsung/samsung-galaxy-s24-ultra-12go_256go-noir-titane.png", false, 'neuf', 5); // Placeholder
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
