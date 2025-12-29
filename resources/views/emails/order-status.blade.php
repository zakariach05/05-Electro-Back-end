<x-mail::message>
# Bonjour {{ $order->customer_name }},

Le statut de votre commande **#{{ $order->id }}** sur Electro-05 a été mis à jour.

**Nouveau statut :** 
<x-mail::panel>
{{ strtoupper($order->status) }}
</x-mail::panel>

@if($order->status == 'processing')
Votre commande a été acceptée par notre équipe. Nous préparons actuellement votre colis pour l'expédition.
@elseif($order->status == 'completed')
Bonne nouvelle ! Votre commande a été livrée avec succès. Merci de votre confiance.
@elseif($order->status == 'cancelled')
Nous sommes au regret de vous informer que votre commande a été annulée. 
@endif

**Récapitulatif de la commande :**
- Montant Total : **{{ $order->total_amount }} DH**
- Destination : {{ $order->customer_city }}

<x-mail::button :url="config('app.url')">
Visiter la boutique
</x-mail::button>

Merci d'avoir choisi Electro-05.<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
