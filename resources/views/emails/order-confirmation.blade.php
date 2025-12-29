<x-mail::message>
# 📧 Facture envoyée avec succès

Merci pour votre commande, **{{ explode(' ', $order->customer_name)[0] }}** !

Votre commande **#ECO-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}** a bien été enregistrée et confirmée.

---

**📦 Résumé de la commande :**
* **Total :** {{ number_format($order->total_amount, 2) }} DH
* **Mode de paiement :** {{ $order->payment_method === 'cod' ? 'Paiement à la livraison + Acompte (100 DH)' : 'Paiement en ligne sécurisé' }}
* **Statut :** En cours de traitement

**📍 Adresse de livraison :**  
{{ $order->customer_address }}, {{ $order->customer_city }}

---

## 🎯 Suivez votre commande en temps réel

<x-mail::button :url="url('/track/' . $order->id . '?token=' . $order->secure_token)">
📱 Suivre ma commande
</x-mail::button>

**Votre facture est jointe à cet email** en format PDF. Vous pouvez également la télécharger à tout moment depuis votre espace de suivi.

---

### ✅ Prochaines étapes :
1. Notre équipe va préparer votre commande
2. Vous recevrez une notification lors de l'expédition
3. Livraison estimée : **2 à 4 jours ouvrables**

Si vous avez des questions, notre équipe est disponible sur WhatsApp ou par email.

Merci pour votre confiance ! 🙏

L'équipe **ELECTRO-05**
</x-mail::message>

