<x-mail::message>
# Nouveau message de contact

Vous avez reçu un nouveau message depuis le formulaire de contact de **Electro-05**.

**Nom :** {{ $data['name'] }}  
**Email :** {{ $data['email'] }}  
**Sujet :** {{ $data['subject'] }}

**Message :**  
{{ $data['message'] }}

<x-mail::button :url="config('app.url')">
Visiter le site
</x-mail::button>

Cordialement,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
