<x-mail::message>
# Nouvelle Commande Reçue !

Une nouvelle commande a été passée sur **Electro-05**.

**📦 Commande :** #{{ $order->id }}  
**👤 Client :** {{ $order->customer_name }}  
**📧 Email :** {{ $order->customer_email }}  
**📞 Téléphone :** {{ $order->customer_phone }}  
**📍 Ville :** {{ $order->customer_city }}

---

**💰 Total :** {{ number_format($order->total_amount, 2) }} DH  
**💳 Paiement :** {{ strtoupper($order->payment_method) }} (Acompte de 100 DH requis)

<x-mail::button :url="config('app.url') . '/admin/orders'">
Voir les détails de la commande
</x-mail::button>

Merci,<br>
Système {{ config('app.name') }}
</x-mail::message>
