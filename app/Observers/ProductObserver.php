<?php

namespace App\Observers;

use App\Models\Product;
use App\Exports\ProductsExport;

// Excel facade is optional; guard calls when package not installed

class ProductObserver
{
    public function created(Product $product)
    {
        // regenerate and store XLSX in public storage
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return;
        }
        try {
            \Maatwebsite\Excel\Facades\Excel::store(new ProductsExport(), 'public/produits_export.xlsx');
        } catch (\Exception $e) {
            // log if needed
        }
    }

    public function updated(Product $product)
    {
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return;
        }
        try {
            \Maatwebsite\Excel\Facades\Excel::store(new ProductsExport(), 'public/produits_export.xlsx');
        } catch (\Exception $e) {
        }
    }

    public function deleted(Product $product)
    {
        if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
            return;
        }
        try {
            \Maatwebsite\Excel\Facades\Excel::store(new ProductsExport(), 'public/produits_export.xlsx');
        } catch (\Exception $e) {
        }
    }
}
