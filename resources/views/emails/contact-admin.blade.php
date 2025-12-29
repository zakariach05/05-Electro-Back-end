<x-mail::message>
# Vous avez reçu un nouveau message

**👤 Nom :** {{ $data['name'] }}  
**📧 Email :** {{ $data['email'] }}  
**📝 Objet :** {{ $data['subject'] ?? 'Sans objet' }}

**💬 Message :**  
{{ $data['message'] }}

<x-mail::button :url="config('app.url') . '/admin/dashboard'">
Accéder au Dashboard
</x-mail::button>

Merci,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
