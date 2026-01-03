<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SubOrder; // Assuming this model exists
use App\Models\OrderItem;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run()
    {
        // Get some products
        $products = Product::inRandomOrder()->take(10)->get();
        if ($products->count() == 0) return;

        // Create Today's Orders (3 orders)
        for ($i = 0; $i < 3; $i++) {
            $this->createOrder($products, now());
        }

        // Create Past Orders (5 orders from previous days)
        for ($i = 0; $i < 5; $i++) {
            $this->createOrder($products, now()->subDays(rand(1, 10)));
        }
    }

    private function createOrder($products, $date)
    {
        $totalAmount = 0;
        
        $order = Order::create([
            'secure_token' => Str::random(32),
            'customer_name' => 'Client Test ' . Str::random(3),
            'customer_email' => 'client' . rand(100, 999) . '@example.com',
            'customer_phone' => '06' . rand(10000000, 99999999),
            'customer_address' => '123 Test Street, Quartier Demo',
            'customer_city' => 'Casablanca',
            'total_amount' => 0, // Will update later
            'status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => $date,
            'updated_at' => $date
        ]);

        // Create SubOrder (assuming single seller for simplicity, or just matching existing structure)
        // Since SubOrders might be created automatically via observers in some apps, 
        // but let's assume we need to create it if the logic requires it.
        // Based on previous AdminOrders code: order.sub_orders.map...
        
        $seller = Seller::first();
        if (!$seller) return;

        $subOrder = SubOrder::create([
            'order_id' => $order->id,
            'seller_id' => $seller->id,
            'subtotal' => 0,
            'status' => 'pending',
            'created_at' => $date,
            'updated_at' => $date
        ]);

        $subTotal = 0;

        // Add 1-3 items
        $orderProducts = $products->random(rand(1, 3));
        foreach ($orderProducts as $product) {
            $qty = rand(1, 2);
            $price = $product->price;
            $itemsTotal = $price * $qty;

            OrderItem::create([
                'order_id' => $order->id,
                'sub_order_id' => $subOrder->id,
                'product_id' => $product->id,
                'quantity' => $qty,
                'price' => $price,
                'created_at' => $date,
                'updated_at' => $date
            ]);

            $subTotal += $itemsTotal;
        }

        $subOrder->update(['subtotal' => $subTotal]);
        
        // Shipping fee logic: if subtotal > 100 or something, but let's just straightforward update
        $totalAmount = $subTotal + 100; // + Shipping
        $order->update(['total_amount' => $totalAmount]);
    }
}
