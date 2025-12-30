<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncFileProductsSeeder extends Seeder
{
    public function run()
    {
        // Adjust this path if necessary to point to storage/app/public/Produit
        $rootPath = storage_path('app/public/Produit');

        if (!File::exists($rootPath)) {
            $this->command->error("Directory not found: $rootPath");
            return;
        }

        $this->command->info("Scanning products in $rootPath...");
        $this->scanDirectory($rootPath);
    }

    private function scanDirectory($path, $parentCategory = null)
    {
        // Process files in current directory
        $items = File::files($path);
        
        foreach ($items as $item) {
            $extension = strtolower($item->getExtension());
            if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'svg'])) {
                if ($parentCategory) {
                    $this->createProduct($item, $parentCategory);
                } else {
                    $this->command->warn("Skipping file at root (no category): " . $item->getFilename());
                }
            }
        }

        // Process subdirectories
        $directories = File::directories($path);
        foreach ($directories as $dir) {
            $dirName = basename($dir);
            
            // Create or Find Category
            // Use slug to avoid duplicates
            $slug = Str::slug($dirName);
            
            $category = Category::firstOrCreate(
                ['slug' => $slug],
                [
                    'name' => $dirName,
                    'parent_id' => $parentCategory ? $parentCategory->id : null,
                ]
            );

            // Recursively scan subdirectories
            $this->scanDirectory($dir, $category);
        }
    }

    private function createProduct($file, $category)
    {
        $filename = $file->getFilenameWithoutExtension();
        // Clean up name
        $name = str_replace(['-', '_', '.'], ' ', $filename);
        $name = trim($name);

        // Calculate relative path for storage
        // storage_path('app/public') needs to be stripped
        $storagePublicPath = storage_path('app/public');
        $fullPath = $file->getPathname();
        
        // Remove base path
        $relativePath = str_replace($storagePublicPath, '', $fullPath);
        // Normalize slashes to forward slashes for URL usage
        $relativePath = str_replace('\\', '/', $relativePath);
        // Remove leading slash if present
        $relativePath = ltrim($relativePath, '/');

        // Check if product with this image already exists to avoid duplicates
        $exists = Product::where('image', $relativePath)->exists();
        
        if (!$exists) {
            // Generate a unique slug
            $slug = Str::slug($name) . '-' . Str::random(6);

            Product::create([
                'name' => $name,
                'slug' => $slug,
                'category_id' => $category->id,
                'image' => $relativePath,
                'price' => rand(100, 10000), // Random price as placeholder
                'stock' => rand(10, 100),    // Random stock
                'state' => 'neuf',
                'delivery_type' => 'Standard',
                'description' => "Description automatique pour $name",
                'is_featured' => false
            ]);
            
            $this->command->info("Added Product: $name");
        }
    }
}
