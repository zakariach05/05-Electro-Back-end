<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;

class ResendOrderInvoice extends Command
{
    protected $signature = 'order:resend-invoice {orderId}';
    protected $description = 'Resend invoice email for a specific order';

    public function handle()
    {
        $orderId = $this->argument('orderId');
        
        try {
            $order = Order::with(['items.product', 'subOrders.seller'])->findOrFail($orderId);
            
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            
            $order->update(['email_sent' => true]);
            
            $this->info("✅ Invoice successfully resent to {$order->customer_email} for order #{$orderId}");
            
        } catch (\Exception $e) {
            $this->error("❌ Failed to resend invoice: " . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}
