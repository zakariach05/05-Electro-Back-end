@component('mail::message')

# Connexion à Electro-05

Cliquez sur le bouton ci-dessous pour vous connecter. Ce lien est à usage unique et expire bientôt.

@component('mail::button', ['url' => $link])
Se connecter
@endcomponent

Si vous n'avez pas demandé ce lien, ignorez ce message.

Merci,
L'équipe Electro-05

@endcomponent
