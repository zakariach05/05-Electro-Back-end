# 🔧 Guide d'Activation de l'Extension PHP GD

## Problème Actuel
Les factures s'affichent en HTML au lieu de PDF car l'extension PHP GD n'est pas activée.

## Solution Rapide

### Étape 1 : Localiser php.ini
```powershell
php --ini
```

Si aucun fichier n'est chargé, créez-en un :
```powershell
# Trouvez le dossier PHP
php -r "echo php_ini_loaded_file();"

# Si vide, créez php.ini dans le dossier PHP
# Copiez php.ini-development en php.ini
```

### Étape 2 : Activer GD
Ouvrez `php.ini` et cherchez :
```ini
;extension=gd
```

Décommentez (enlevez le `;`) :
```ini
extension=gd
```

### Étape 3 : Redémarrer
```bash
# Arrêtez le serveur (Ctrl+C)
php artisan serve
```

### Étape 4 : Vérifier
```bash
php -m | findstr gd
```

## Alternative : Utiliser XAMPP/Laragon
Si vous utilisez XAMPP ou Laragon, GD est généralement déjà inclus.
Il suffit de l'activer dans le panneau de configuration.

## Résultat Attendu
✅ Factures téléchargeables en PDF
✅ Emails avec pièce jointe PDF
✅ QR codes fonctionnels

## État Actuel (Sans GD)
✅ Factures visibles en HTML
✅ Bouton d'impression disponible
✅ Emails envoyés (sans PDF)
✅ Système 100% fonctionnel
