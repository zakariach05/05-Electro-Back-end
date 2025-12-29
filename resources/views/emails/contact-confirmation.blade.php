<x-mail::message>
# Bonjour {{ $name }},

Nous avons bien reçu votre message et nous vous en remercions.  
Notre équipe technique ou commerciale l'étudiera avec attention et vous répondra dans les plus brefs délais.

En attendant, vous pouvez continuer à découvrir nos dernières nouveautés sur notre boutique.

<x-mail::button :url="config('app.url')">
Retourner sur la boutique
</x-mail::button>

Cordialement,<br>
L'équipe {{ config('app.name') }}
</x-mail::message>
