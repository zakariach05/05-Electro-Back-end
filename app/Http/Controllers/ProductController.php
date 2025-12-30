<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Exports\ProductsExport;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    // List all products
    public function index(Request $request)
    {
        $query = Product::with(['category', 'seller']);

        // Filter by Price
        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        // Filter by State
        if ($request->has('states')) {
            $states = explode(',', $request->states);
            $query->whereIn('state', $states);
        }

        // Filter by Brand (Search in name)
        if ($request->has('brands')) {
            $brands = explode(',', $request->brands);
            $query->where(function ($q) use ($brands) {
                foreach ($brands as $brand) {
                    $q->orWhere('name', 'LIKE', "%{$brand}%");
                }
            });
        }

        // Filter by Category
        if ($request->has('category')) {
            $query->whereHas('category', function ($q) use ($request) {
                $q->where('slug', $request->category);
            });
        }

        // Filter by Featured
        if ($request->has('featured')) {
            $query->where('is_featured', true);
        }

        // Filter by Promo
        if ($request->has('promo')) {
            $query->where('promo', '>', 0);
        }

        // Filter by Stock
        if ($request->has('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Global Search (Name or Description)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('slug', 'LIKE', "%{$searchTerm}%");
            });
        }

        return response()->json($query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 12));
    }

    // Get single product details
    public function show($id)
    {
        $product = Product::with(['category', 'seller'])->findOrFail($id);
        return response()->json($product);
    }

    public function store(Request $request)
    {
        try {
            // Validate core fields first (don't validate `image` as string here to avoid
            // issues when multipart data is parsed differently by PHP)
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category_id' => 'required|exists:categories,id',
                'price' => 'required|numeric',
                'stock' => 'required|integer',
                'image_file' => 'nullable|image',
                'description' => 'nullable|string',
                'state' => 'nullable|string',
                'is_featured' => 'nullable|boolean',
                'old_price' => 'nullable|numeric',
            ]);

            // Ensure we have either an image URL (string) or an uploaded file
            $hasUrl = $request->filled('image') && is_string($request->input('image')) && $request->input('image') !== '';
            $hasFile = $request->hasFile('image_file');
            if (! $hasUrl && ! $hasFile) {
                return response()->json([
                    'message' => 'Les données fournies sont invalides.',
                    'errors' => ['image' => ['Either image (URL) or image_file (upload) is required.']]
                ], 422);
            }

            // If an uploaded file was provided, store it under a folder for the
            // product's category (if available), otherwise default to 'products'.
            if ($hasFile) {
                $file = $request->file('image_file');
                $category = Category::find($validated['category_id']);
                $folder = 'products';
                if ($category) {
                    // use category slug (safe) as subfolder
                    $slug = $category->slug ?: Str::slug($category->name);
                    $folder = "products/{$slug}";
                }
                $path = $file->store($folder, 'public');
                // store a publicly accessible full URL (uses APP_URL)
                $validated['image'] = asset('storage/' . $path);
            } elseif ($hasUrl) {
                // use provided image URL as-is
                $validated['image'] = $request->input('image');
            }

            // Auto-generate slug if not provided
            if (!$request->has('slug')) {
                $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']) . '-' . rand(100, 999);
            }

            $product = Product::create($validated);

            // Update product stock export (CSV). We append the new product.
            $this->appendProductToStockFile($product);

            return response()->json($product, 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Les données fournies sont invalides.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Une erreur est survenue lors de l\'enregistrement.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        // allow updating via file upload as well
        $data = $request->all();
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            // try to use category folder if provided in payload or existing product
            $categoryId = $request->input('category_id', $product->category_id);
            $category = Category::find($categoryId);
            $folder = 'products';
            if ($category) {
                $slug = $category->slug ?: Str::slug($category->name);
                $folder = "products/{$slug}";
            }
            $path = $file->store($folder, 'public');
            $data['image'] = '/storage/' . $path;
        }
        $product->update($data);

        // Rebuild the stock export so it reflects current DB state (update case)
        $this->rebuildStockFile();
        return response()->json($product);
    }

    /**
     * Append a single product row to the CSV stock file in storage/app/public
     */
    protected function appendProductToStockFile(Product $product)
    {
        try {
            $filePath = storage_path('app/public/products_stock.csv');
            $isNew = !file_exists($filePath);
            $handle = fopen($filePath, 'a');
            if ($isNew && $handle) {
                // write UTF-8 BOM so Excel recognises encoding, then header with semicolon delimiter
                fwrite($handle, "\xEF\xBB\xBF");
                fputcsv($handle, ['id_produit', 'nom_produit', 'categorie', 'prix', 'stock', 'statut', 'created_at'], ';');
            }
            if ($handle) {
                $category = $product->category ? $product->category->name : '';
                $row = [
                    $product->id,
                    str_replace(';', ',', $product->name),
                    str_replace(';', ',', $category),
                    $product->price,
                    $product->stock,
                    $product->state ?? '',
                    $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '',
                ];
                fputcsv($handle, $row, ';');
                fclose($handle);
            }
            // ensure the file is publicly accessible via storage:link
        } catch (\Exception $e) {
            // silently fail but log in real app
        }
    }

    /**
     * Rebuild the entire stock CSV from DB (used after updates).
     */
    protected function rebuildStockFile()
    {
        try {
            $filePath = storage_path('app/public/products_stock.csv');
            $handle = fopen($filePath, 'w');
            if (! $handle) {
                return;
            }
            // write BOM for Excel/Windows and use semicolon as delimiter (French locales expect ';')
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['id_produit', 'nom_produit', 'categorie', 'prix', 'stock', 'statut', 'created_at'], ';');
            $products = Product::with('category')->orderBy('id')->get();
            foreach ($products as $product) {
                $category = $product->category ? $product->category->name : '';
                $row = [
                    $product->id,
                    str_replace(';', ',', $product->name),
                    str_replace(';', ',', $category),
                    $product->price,
                    $product->stock,
                    $product->state ?? '',
                    $product->created_at ? $product->created_at->format('Y-m-d H:i:s') : '',
                ];
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        } catch (\Exception $e) {
            // silently fail
        }
    }

    /**
     * Export the stock file (CSV). Protected route for admin.
     * If file doesn't exist, rebuild it first.
     */
    public function exportStock(Request $request)
    {
        // If client requested xlsx explicitly, return XLSX download
        if ($request->query('format') === 'xlsx' || $request->query('type') === 'xlsx') {
            // If the XLSX package is not installed, fallback to CSV download
            if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                // always rebuild to ensure header uses semicolon + BOM
                $this->rebuildStockFile();
                $filePath = storage_path('app/public/products_stock.csv');
                if (!file_exists($filePath)) {
                    return response()->json(['message' => 'Fichier d\'inventaire introuvable.'], 404);
                }
                // return CSV as fallback with a descriptive filename
                return response()->download($filePath, 'produits_export.csv', [
                    'Content-Type' => 'text/csv; charset=UTF-8',
                ]);
            }
            // regenerate file to ensure fresh data
            try {
                // stream download
                return \Maatwebsite\Excel\Facades\Excel::download(new ProductsExport(), 'produits_export.xlsx');
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erreur lors de la génération du fichier XLSX.', 'error' => $e->getMessage()], 500);
            }
        }

        // Fallback: return CSV if exists
        // ensure file is rebuilt so header and delimiters are correct
        $this->rebuildStockFile();
        $filePath = storage_path('app/public/products_stock.csv');
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Fichier d\'inventaire introuvable.'], 404);
        }
        return response()->download($filePath, 'produits_export.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Produit supprimé']);
    }
}
