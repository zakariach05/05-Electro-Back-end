<x-mail::message>
# Nouveau message de contact

**Nom :** {{ $data['name'] }}  
**Email :** {{ $data['email'] }}  
**Sujet :** {{ $data['subject'] ?? 'N/A' }}

**Message :**  
{{ $data['message'] }}

<x-mail::button :url="config('app.url')">
Retour sur le site
</x-mail::button>

Merci,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
