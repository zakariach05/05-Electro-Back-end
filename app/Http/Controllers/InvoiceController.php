<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderConfirmationMail;

class InvoiceController extends Controller
{
    public function resend(Request $request, $id)
    {
        try {
            $order = Order::with(['items.product', 'subOrders.seller'])->findOrFail($id);
            
            Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));
            
            $order->update(['email_sent' => true]);
            
            return response()->json([
                'message' => 'Facture renvoyée avec succès à ' . $order->customer_email
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors du renvoi de la facture: ' . $e->getMessage()
            ], 500);
        }
    }
}
