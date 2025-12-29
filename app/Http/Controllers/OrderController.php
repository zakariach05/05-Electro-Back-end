<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Mail\OrderAdminMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderStatusMail;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Exports\OrdersExport;
use Maatwebsite\Excel\Facades\Excel;

class OrderController extends Controller
{
    private function logActivity($action, $targetType, $targetId, $details = null)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'details' => $details,
                'ip_address' => request()->ip()
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Activity Log error: " . $e->getMessage());
        }
    }

    private function calculateDeliveryEstimate($sellerCity, $customerCity, $prepDays, $deliveryType)
    {
        $shippingDays = 1;

        if (strtolower($sellerCity) !== strtolower($customerCity)) {
            $shippingDays += 1;
        }

        if ($deliveryType === 'standard') {
            $shippingDays += 1;
        }

        $min = $prepDays + $shippingDays;
        $max = $min + 2;

        return "Livraison estimée : $min à $max jours ouvrables";
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required|string',
            'address' => 'required|string',
            'city' => 'required|string',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $total = 0;
            foreach ($request->items as $item) {
                $total += $item['price'] * $item['quantity'];
            }
            $total += 100; // Add 100 DH delivery fee

            $secureToken = Str::random(64);

            $order = Order::create([
                'customer_name' => $validated['firstName'] . ' ' . $validated['lastName'],
                'customer_email' => $validated['email'],
                'customer_phone' => $validated['phone'],
                'customer_address' => $validated['address'],
                'customer_city' => $validated['city'],
                'total_amount' => $total,
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_method'] === 'online' ? 'paid' : 'unpaid',
                'acompte_paid' => $validated['payment_method'] === 'online',
                'order_notes' => $request->order_notes ?? '',
                'secure_token' => $secureToken,
                'email_sent' => false
            ]);

            // Group items by seller
            $sellerItems = [];
            foreach ($request->items as $itemData) {
                $product = \App\Models\Product::with('seller')->find($itemData['id']);
                $sellerId = $product->seller_id ?? 1; // fallback to admin (1) if no seller set

                // Ensure we always have a seller object with expected properties
                $sellerObj = $product->seller ?? (object) ['city' => '', 'prep_days' => 0];

                if (!isset($sellerItems[$sellerId])) {
                    $sellerItems[$sellerId] = [
                        'seller' => $sellerObj,
                        'items' => [],
                        'subtotal' => 0
                    ];
                }
                $sellerItems[$sellerId]['items'][] = [
                    'product_id' => $product->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $itemData['price'],
                    'delivery_type' => $product->delivery_type
                ];
                $sellerItems[$sellerId]['subtotal'] += $itemData['price'] * $itemData['quantity'];
            }

            // Create sub-orders
            foreach ($sellerItems as $sellerId => $data) {
                $estimate = $this->calculateDeliveryEstimate(
                    $data['seller']->city,
                    $order->customer_city,
                    $data['seller']->prep_days,
                    $data['items'][0]['delivery_type'] // Use first item's delivery type
                );

                $subOrder = \App\Models\SubOrder::create([
                    'order_id' => $order->id,
                    'seller_id' => $sellerId,
                    'subtotal' => $data['subtotal'],
                    'status' => 'pending',
                    'delivery_estimate' => $estimate
                ]);

                foreach ($data['items'] as $item) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'sub_order_id' => $subOrder->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
            }

            DB::commit();

            try {
                // Send admin notification
                Mail::to('chzakaria037@gmail.com')->send(new OrderAdminMail($order));

                // Send customer confirmation with invoice PDF
                Mail::to($order->customer_email)->send(new OrderConfirmationMail($order));

                // Mark email as sent
                $order->update(['email_sent' => true]);

            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Mail error: " . $e->getMessage());
            }

            return response()->json(['message' => 'Order created', 'order_id' => $order->id], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error("Order store error: " . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index()
    {
        return Order::with(['items.product', 'subOrders.seller'])->latest()->get();
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'subOrders.seller', 'subOrders.items.product'])->findOrFail($id);

        // Verify secure token if provided
        $token = $request->query('token');
        if ($token && $order->secure_token !== $token) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        return $order;
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|in:pending,processing,completed,cancelled']);
        $order = Order::findOrFail($id);
        $oldStatus = $order->status;
        $order->status = $request->status;
        $order->save();

        $this->logActivity("Update Status", "Order", $id, "Status changed from $oldStatus to {$request->status}");

        try {
            Mail::to($order->customer_email)->send(new OrderStatusMail($order));
        } catch (\Exception $e) { }

        return response()->json(['message' => 'Statut mis à jour', 'order' => $order]);
    }

    public function assignAgent(Request $request, $id)
    {
        $request->validate(['agent' => 'required|string']);
        $order = Order::findOrFail($id);
        $order->assigned_agent = $request->agent;
        $order->save();

        $this->logActivity("Assign Agent", "Order", $id, "Assigned to {$request->agent}");

        return response()->json(['message' => 'Livreur/Agence assigné', 'order' => $order]);
    }

    public function updatePayment(Request $request, $id)
    {
        $request->validate(['payment_status' => 'required|in:unpaid,paid,refunded']);
        $order = Order::findOrFail($id);
        $order->payment_status = $request->payment_status;
        if ($request->payment_status === 'paid') $order->acompte_paid = true;
        $order->save();

        $this->logActivity("Update Payment", "Order", $id, "Payment status: {$request->payment_status}");

        return response()->json(['message' => 'Paiement mis à jour', 'order' => $order]);
    }

    public function refund(Request $request, $id)
    {
        $request->validate(['amount' => 'required|numeric|min:0']);
        $order = Order::findOrFail($id);
        $order->refunded_amount = $request->amount;
        $order->payment_status = 'refunded';
        $order->save();

        $this->logActivity("Refund", "Order", $id, "Refunded amount: {$request->amount} DH");

        return response()->json(['message' => 'Remboursement enregistré', 'order' => $order]);
    }

    public function viewInvoice(Request $request, $id)
    {
        try {
            $order = Order::with(['items.product', 'subOrders.seller'])->findOrFail($id);

            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                return view('emails.invoice', compact('order'));
            }

            $pdf = Pdf::loadView('emails.invoice', compact('order'));
            return $pdf->stream("Facture_Electro05_{$order->id}.pdf");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Invoice view error: " . $e->getMessage());

            try {
                $order = Order::with(['items.product', 'subOrders.seller'])->findOrFail($id);
                return view('emails.invoice', compact('order'));
            } catch (\Exception $fallbackError) {
                return response()->json(['error' => 'Erreur: ' . $e->getMessage()], 500);
            }
        }
    }

    public function generateInvoice($id)
    {
        try {
            $order = Order::with(['items.product', 'subOrders.seller'])->findOrFail($id);

            // Check if GD extension is available
            if (!extension_loaded('gd')) {
                // Return HTML view instead of PDF
                return view('emails.invoice', compact('order'));
            }

            $pdf = Pdf::loadView('emails.invoice', compact('order'));
            return $pdf->download("Facture_Electro05_{$order->id}.pdf");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Invoice generation error: " . $e->getMessage());

            // Fallback to HTML view on any error
            try {
                $order = Order::with(['items.product', 'subOrders.seller'])->findOrFail($id);
                return view('emails.invoice', compact('order'));
            } catch (\Exception $fallbackError) {
                return response()->json(['error' => 'Erreur lors de la génération de la facture: ' . $e->getMessage()], 500);
            }
        }
    }

    /**
     * Export orders list as CSV or XLSX (admin only)
     */
    public function exportOrders(Request $request)
    {
        // If XLSX requested and package available
        if ($request->query('format') === 'xlsx' || $request->query('type') === 'xlsx') {
            if (!class_exists(\Maatwebsite\Excel\Facades\Excel::class)) {
                // fallback to CSV
                $this->rebuildOrdersCsv();
                $filePath = storage_path('app/public/orders_export.csv');
                if (!file_exists($filePath)) {
                    return response()->json(['message' => 'Fichier commandes introuvable.'], 404);
                }
                return response()->download($filePath, 'orders_export.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
            }
            try {
                return Excel::download(new OrdersExport(), 'orders_export.xlsx');
            } catch (\Exception $e) {
                return response()->json(['message' => 'Erreur génération XLSX', 'error' => $e->getMessage()], 500);
            }
        }

        // Always rebuild CSV to ensure header and format
        $this->rebuildOrdersCsv();
        $filePath = storage_path('app/public/orders_export.csv');
        if (!file_exists($filePath)) {
            return response()->json(['message' => 'Fichier commandes introuvable.'], 404);
        }
        return response()->download($filePath, 'orders_export.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    protected function rebuildOrdersCsv()
    {
        try {
            $filePath = storage_path('app/public/orders_export.csv');
            $handle = fopen($filePath, 'w');
            if (! $handle) return;
            // BOM + header
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['id_commande', 'reference', 'client', 'montant', 'statut', 'paiement', 'date_creation'], ';');
            $orders = Order::orderBy('id')->get();
            foreach ($orders as $order) {
                $row = [
                    $order->id,
                    $order->secure_token ?? '',
                    str_replace(';', ',', $order->customer_name ?? ''),
                    number_format($order->total_amount, 2, '.', ''),
                    $order->status ?? '',
                    $order->payment_status ?? '',
                    $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                ];
                fputcsv($handle, $row, ';');
            }
            fclose($handle);
        } catch (\Exception $e) {
            // log but do not break
        }
    }
}
