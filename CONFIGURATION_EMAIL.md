# Configuration Email pour Electro-05

## 🔧 Problème actuel

Votre configuration email utilise un mot de passe Gmail normal (`48Pgvv99`), mais **Google n'autorise plus les applications à se connecter avec un mot de passe normal** pour des raisons de sécurité.

## ✅ Solution : Utiliser un "App Password" (Mot de passe d'application)

### Étape 1 : Activer la validation en 2 étapes sur votre compte Google

1. Allez sur [myaccount.google.com](https://myaccount.google.com)
2. Cliquez sur **Sécurité** dans le menu de gauche
3. Sous "Connexion à Google", activez la **Validation en deux étapes**
4. Suivez les instructions pour configurer la validation (SMS ou application)

### Étape 2 : Générer un mot de passe d'application

1. Retournez sur [myaccount.google.com/security](https://myaccount.google.com/security)
2. Sous "Connexion à Google", cliquez sur **Mots de passe des applications**
3. Sélectionnez :
   - **Application** : Autre (nom personnalisé)
   - **Nom** : Electro-05
4. Cliquez sur **Générer**
5. Google va afficher un mot de passe de 16 caractères (exemple : `abcd efgh ijkl mnop`)
6. **Copiez ce mot de passe** (sans les espaces)

### Étape 3 : Mettre à jour votre fichier `.env`

Remplacez la ligne suivante dans votre fichier `.env` :

```env
MAIL_PASSWORD=48Pgvv99
```

Par :

```env
MAIL_PASSWORD=abcdefghijklmnop
```

(Utilisez le mot de passe généré à l'étape 2, sans espaces)

### Étape 4 : Vider le cache Laravel

Exécutez cette commande :

```bash
php artisan config:clear
```

## 📧 Configuration actuelle

Voici votre configuration email actuelle dans `.env` :

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=465
MAIL_USERNAME=chzakaria037@gmail.com
MAIL_PASSWORD=48Pgvv99  ← À REMPLACER PAR UN APP PASSWORD
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="chzakaria037@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## ✅ Emails configurés

Tous les emails sont maintenant envoyés à l'adresse admin officielle :

- **Messages de contact** → `chzakaria037@gmail.com`
- **Notifications de commande** → `chzakaria037@gmail.com`
- **Confirmations clients** → Email du client

## 🧪 Test après configuration

Une fois le mot de passe d'application configuré, testez l'envoi d'email :

1. Remplissez le formulaire de contact sur votre site
2. Vérifiez votre boîte mail `chzakaria037@gmail.com`
3. Le client devrait aussi recevoir un email de confirmation

## ⚠️ Important

- **NE PARTAGEZ JAMAIS** votre mot de passe d'application
- Si vous pensez qu'il a été compromis, révoquez-le et générez-en un nouveau
- Conservez ce mot de passe en lieu sûr (gestionnaire de mots de passe)
