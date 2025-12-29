<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture #{{ $order->id }} - ELECTRO-05</title>
    <style>
        /* Modern Typography & Reset */
        @page {
            margin: 0cm 0cm;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
            background: #ffffff;
            line-height: 1.5;
        }

        .container {
            padding: 40px 50px;
        }

        /* Top Accent Bar */
        .color-bar {
            height: 8px;
            background: linear-gradient(90deg, #3b82f6 0%, #1e40af 100%);
            width: 100%;
        }

        /* Layout Tables */
        .full-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }

        .header-section {
            padding-bottom: 50px;
            border-bottom: 1px solid #f3f4f6;
        }

        .logo-text {
            font-size: 32px;
            font-weight: 900;
            color: #1d4ed8;
            letter-spacing: -1px;
        }

        .invoice-title {
            font-size: 24px;
            font-weight: 300;
            color: #6b7280;
            text-align: right;
            text-transform: uppercase;
        }

        /* Info Blocks */
        .info-grid {
            margin-top: 40px;
            margin-bottom: 40px;
        }

        .info-label {
            font-size: 10px;
            font-weight: 800;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .info-content {
            font-size: 13px;
            color: #374151;
            font-weight: 500;
        }

        /* Product Table with Simple Borders */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            border: 1px solid #000;
        }

        .items-table th {
            background: #f2f2f2;
            color: #000;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: left;
            padding: 10px;
            border: 1px solid #000;
        }

        .items-table td {
            padding: 10px;
            font-size: 12px;
            border: 1px solid #000;
        }

        .item-name {
            font-weight: 700;
            color: #000;
        }

        /* Totals Area */
        .totals-section {
            margin-top: 20px;
            float: right;
            width: 250px;
            border: 1px solid #000;
            padding: 10px;
        }

        .total-row {
            padding: 5px 0;
            font-size: 14px;
        }

        .total-label {
            color: #000;
            text-align: left;
            font-weight: bold;
        }

        .total-value {
            text-align: right;
            font-weight: 700;
        }

        .grand-total {
            border-top: 2px solid #000;
            margin-top: 5px;
            padding-top: 10px;
            font-size: 18px;
            font-weight: 900;
            color: #000;
        }

        /* Footer */
        .footer {
            position: absolute;
            bottom: 40px;
            left: 50px;
            right: 50px;
            text-align: center;
            border-top: 1px solid #000;
            padding-top: 20px;
            font-size: 11px;
            color: #000;
        }

        /* Utils */
        .text-right { text-align: right; }
        .clear { clear: both; }
        
        /* Print button - hide when printing */
        .no-print {
            display: block;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }

    </style>
</head>
<body>
    <div class="color-bar"></div>
    
    <!-- Info Banner (hidden when printing) -->
    <div class="no-print" style="background: #eff6ff; border-bottom: 2px solid #3b82f6; padding: 15px 50px; text-align: center;">
        <p style="margin: 0; font-size: 13px; color: #1e40af; font-weight: 700;">
            📄 Aperçu HTML de la facture • 
            <button onclick="window.print()" style="background: #3b82f6; color: white; border: none; padding: 8px 20px; border-radius: 8px; font-weight: 700; cursor: pointer; margin-left: 10px;">
                🖨️ Imprimer / Sauvegarder en PDF
            </button>
        </p>
    </div>
    
    <div class="container">
        <!-- Header -->
        <table class="full-table">
            <tr>
                <td width="50%">
                    @php
                        $logoPath = public_path('logo.png');
                        $logoExists = file_exists($logoPath);
                        $logoBase64 = null;
                        
                        if ($logoExists) {
                            try {
                                $imageData = file_get_contents($logoPath);
                                $logoBase64 = 'data:image/png;base64,' . base64_encode($imageData);
                            } catch (\Exception $e) {
                                $logoBase64 = null;
                            }
                        }
                    @endphp
                    
                    @if($logoBase64)
                        <img src="{{ $logoBase64 }}" alt="ELECTRO05" style="max-width: 180px; height: auto;" />
                    @else
                        <div style="font-size: 36px; font-weight: 900; color: #1d4ed8; letter-spacing: -2px; line-height: 1;">
                            ELECTRO<span style="color: #3b82f6;">05</span>
                        </div>
                        <div style="font-size: 10px; color: #000; text-transform: uppercase; letter-spacing: 3px; margin-top: 5px; font-weight: 700;">
                            PREMIUM HIGH-TECH
                        </div>
                    @endif
                </td>
                <td width="50%" class="text-right">
                    <div class="invoice-title">Facture</div>
                    <div class="info-content">#FA-{{ $order->id }}-{{ date('Y') }}</div>
                    <div class="info-content" style="color: #9ca3af;">Date: {{ $order->created_at->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- Info Grid -->
        <table class="full-table info-grid">
            <tr>
                <td width="45%" valign="top">
                    <div class="info-label">Émetteur</div>
                    <div class="info-content" style="font-weight: 700; font-size: 15px;">ELECTRO-05 Maroc</div>
                    <div class="info-content">Avenue Mohamed V, Casablanca</div>
                    <div class="info-content">Contact@electro05.ma</div>
                    <div class="info-content">+212 5XX XX XX XX</div>
                </td>
                <td width="10%"></td>
                <td width="45%" valign="top">
                    <div class="info-label">Client</div>
                    <div class="info-content" style="font-weight: 700; font-size: 15px;">{{ $order->customer_name }}</div>
                    <div class="info-content">{{ $order->customer_email }}</div>
                    <div class="info-content">{{ $order->customer_phone }}</div>
                    <div class="info-content">{{ $order->customer_address }}, {{ $order->customer_city }}</div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th width="55%">Description de l'article / Vendeur</th>
                    <th width="10%" class="text-right">Quantité</th>
                    <th width="15%" class="text-right">Prix Unitaire</th>
                    <th width="20%" class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->subOrders as $subOrder)
                    <tr style="background: #f8fafc;">
                        <td colspan="4" style="padding: 8px 15px; border-bottom: 2px solid #e2e8f0;">
                            <span style="font-size: 10px; font-weight: 800; color: #3b82f6; text-transform: uppercase;">
                                Expedition par : {{ $subOrder->seller->name }} ({{ $subOrder->seller->city }})
                            </span>
                            <span style="float: right; font-size: 9px; color: #64748b; font-weight: 700;">
                                {{ $subOrder->delivery_estimate }}
                            </span>
                        </td>
                    </tr>
                    @foreach($order->items->filter(fn($i) => $i->sub_order_id == $subOrder->id) as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product->name }}</div>
                            <div style="font-size: 10px; color: #9ca3af; margin-top: 4px;">Code: PRD-{{ $item->product->id }}</div>
                        </td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }} DH</td>
                        <td class="text-right" style="font-weight: 700;">{{ number_format($item->price * $item->quantity, 2) }} DH</td>
                    </tr>
                    @endforeach
                @endforeach

                @if($order->subOrders->isEmpty())
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div class="item-name">{{ $item->product->name }}</div>
                            <div style="font-size: 10px; color: #9ca3af; margin-top: 4px;">Code: PRD-{{ $item->product->id }}</div>
                        </td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->price, 2) }} DH</td>
                        <td class="text-right" style="font-weight: 700;">{{ number_format($item->price * $item->quantity, 2) }} DH</td>
                    </tr>
                    @endforeach
                @endif
                
                <tr>
                    <td>
                        <div class="item-name" style="color: #6b7280;">Logistique & Livraison</div>
                        <div style="font-size: 10px; color: #9ca3af; margin-top: 4px;">Service de livraison express centralisé</div>
                    </td>
                    <td class="text-right">1</td>
                    <td class="text-right">100.00 DH</td>
                    <td class="text-right" style="font-weight: 700;">100.00 DH</td>
                </tr>
            </tbody>
        </table>

        <!-- Totals Area -->
        <div class="totals-section">
            <table width="100%">
                <tr>
                    <td class="total-label">Sous-total HT</td>
                    <td class="total-value">{{ number_format($order->total_amount - 100, 2) }} DH</td>
                </tr>
                <tr>
                    <td class="total-label">Frais de Livraison</td>
                    <td class="total-value">100.00 DH</td>
                </tr>
                <tr class="grand-total">
                    <td style="padding-top: 15px;">TOTAL FINAL</td>
                    <td class="text-right" style="padding-top: 15px;">{{ number_format($order->total_amount, 2) }} DH</td>
                </tr>
            </table>
            
            <div style="margin-top: 30px; border: 1px solid #e5e7eb; padding: 15px; border-radius: 10px; background: #fdfdfd;">
                <div class="info-label" style="margin-bottom: 5px;">Mode de Règlement</div>
                <div class="info-content" style="font-size: 11px;">
                    @if($order->payment_status === 'paid')
                        ✅ Payé intégralement via Paiement Sécurisé
                    @else
                        💵 Paiement à la réception (Cash on Delivery)
                    @endif
                </div>
            </div>
        </div>

        <div class="clear"></div>

        <!-- Tracking Section -->
        <div style="margin-top: 40px; padding: 20px; border: 1px solid #000; text-align: center; background: #f9fafb;">
            <h3 style="font-size: 14px; font-weight: 900; margin: 0 0 15px 0; text-transform: uppercase; color: #1d4ed8;">🔍 Suivi de Commande</h3>
            
            @php
                $trackingUrl = url('/track/' . $order->id . '?token=' . $order->secure_token);
            @endphp
            
            <div style="background: #fff; padding: 15px; border-radius: 8px; margin: 10px 0; border: 2px dashed #3b82f6;">
                <p style="font-size: 11px; color: #6b7280; margin: 0 0 8px 0; font-weight: 700; text-transform: uppercase;">Lien de Suivi Sécurisé</p>
                <p style="font-size: 10px; color: #1d4ed8; margin: 0; word-break: break-all; font-weight: 600;">
                    {{ $trackingUrl }}
                </p>
            </div>
            
            <p style="font-size: 10px; color: #000; margin: 15px 0 0 0; line-height: 1.6;">
                📱 <strong>Suivez votre commande en temps réel</strong><br/>
                Copiez ce lien dans votre navigateur ou scannez le QR code envoyé par email<br/>
                <strong style="color: #1d4ed8;">ID: #ECO-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</strong>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="margin-bottom: 5px; font-weight: 700; color: #4b5563;">Merci pour votre confiance chez ELECTRO-05 !</p>
            <p>Cette facture est générée électroniquement. Conditions générales de vente disponibles sur www.electro05.ma</p>
            <p style="margin-top: 10px;">ELECTRO-05 - Votre partenaire High-Tech de confiance au Maroc.</p>
        </div>
    </div>
</body>
</html>
