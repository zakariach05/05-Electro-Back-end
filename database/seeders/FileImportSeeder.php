<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Models\Seller;

class FileImportSeeder extends Seeder
{
    private $categoryMapping = [
        'phone' => 'smartphones',
        'phones' => 'smartphones',
        'apple' => 'smartphones',
        'samsung' => 'smartphones',
        'pc' => 'pc-mac',
        'mac' => 'pc-mac',
        'laptop' => 'pc-mac',
        'gamer' => 'pc-mac',
        'gaming' => 'gaming',
        'jeu' => 'gaming',
        'audio' => 'accessories',
        'casque' => 'accessories',
        'airpods' => 'accessories',
        'tv' => 'tv',
        'video' => 'tv',
        'tablette' => 'tablettes-accessoires',
        'tablet' => 'tablettes-accessoires',
        'ipad' => 'tablettes-accessoires',
    ];

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

        // Ensure Default Seller
        $seller = Seller::create([
            'name' => 'Electro-05',
            'city' => 'Casablanca',
            'email' => 'contact@electro05.ma',
            'prep_days' => 1
        ]);

        $rootPath = storage_path('app/public/Produit');
        if (!File::exists($rootPath)) return;

        $this->importFromDirectory($rootPath);
        
        // --- SELECT PROMO WINNERS FOR HERO ---
        // We need 1 of each type for the hero/promo page initial display
        $this->selectFeaturedPromos();
    }

    private function importFromDirectory($path, $parentId = null)
    {
        $files = File::files($path);
        foreach ($files as $file) {
            if (in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp', 'avif'])) {
                if (strtolower($file->getFilename()) === 'cover.png') continue;
                if ($parentId) $this->createProductFromFile($file, $parentId);
            }
        }

        $dirs = File::directories($path);
        foreach ($dirs as $dir) {
            $dirName = basename($dir);
            $slug = Str::slug($dirName);

            foreach ($this->categoryMapping as $key => $target) {
                if (str_contains(strtolower($dirName), $key)) {
                    $slug = $target;
                    break;
                }
            }

            $category = Category::updateOrCreate(
                ['slug' => $slug],
                ['name' => $parentId ? $dirName : $this->getBetterName($dirName), 'parent_id' => $parentId, 'image' => 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?q=80&w=800']
            );

            if (File::exists($dir . '/cover.png')) {
                $category->update(['image' => 'Produit/' . basename($dir) . '/cover.png']);
            }

            $this->importFromDirectory($dir, $category->id);
        }
    }

    private function getBetterName($dirName)
    {
        $low = strtolower($dirName);
        if (str_contains($low, 'phone')) return 'Smartphones';
        if (str_contains($low, 'pc')) return 'Informatique';
        if (str_contains($low, 'audio')) return 'Audio & Hi-Fi';
        if (str_contains($low, 'jeu') || str_contains($low, 'gaming')) return 'Gaming & Consoles';
        if (str_contains($low, 'tv')) return 'TV & Vidéo';
        return ucwords($dirName);
    }

    private function createProductFromFile($file, $categoryId)
    {
        $name = pathinfo($file->getFilename(), PATHINFO_FILENAME);
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords(strtolower($name));

        $relPath = str_replace(storage_path('app/public/'), '', $file->getPathname());
        $imageUrl = str_replace('\\', '/', $relPath);

        Product::create([
            'category_id' => $categoryId,
            'seller_id' => Seller::first()->id,
            'name' => $name,
            'slug' => Str::slug($name) . '-' . rand(100, 999),
            'description' => "Le $name est disponible chez Electro-05.",
            'price' => rand(5000, 20000),
            'image' => $imageUrl,
            'stock' => rand(5, 50),
            'is_featured' => false,
            'promo' => 0
        ]);
    }

    private function selectFeaturedPromos()
    {
        $types = [
            'tv' => 'TV',
            'iphone' => 'Apple',
            'samsung' => 'Samsung',
            'tablet' => 'Tablet',
            'macbook' => 'PC',
            'gamer' => 'Gamer',
            'airpods' => 'Airpods',
        ];

        foreach ($types as $key => $label) {
            // Get many products of this type
            $products = Product::where('name', 'LIKE', "%$key%")->take(6)->get();
            
            foreach ($products as $index => $product) {
                // Only the first one is "featured" (shown immediately on promo page)
                // All others are promos but hidden until the code is entered
                $product->update([
                    'is_featured' => ($index === 0), 
                    'promo' => rand(15, 35),
                    'old_price' => $product->price * (1 + (rand(10, 25) / 100))
                ]);
            }
        }
        
        // Ensure some Gaming consoles are also in the mix
        $consoles = Product::where('name', 'LIKE', '%ps5%')->orWhere('name', 'LIKE', '%xbox%')->get();
        foreach ($consoles as $index => $console) {
            $console->update([
                'is_featured' => ($index === 0),
                'promo' => rand(5, 15),
                'old_price' => $console->price * 1.10
            ]);
        }
    }
}
