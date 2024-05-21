# Installation en local (dev)

## Récupérer le code du site en local

Créer un dossier local pour le projet.

```shell
git init --initial-branch=develop

# Récupération du code du projet
git remote add origin git@github.com:Archi-Strasbourg/archi-mediawiki.git
git pull origin develop
git branch --set-upstream-to=origin/develop develop
```

## Installer le site en local

```shell
# Téléchargement des dépendances externes
composer install
```

À partir de cette étape, vous devez avoir un serveur Apache
configuré pour pointer vers le dossier dans lequel le projet
est installé
ainsi qu'un serveur SQL fonctionnel.

On part du principe que le site est accessible sur une URL du type
<http://localhost/sous-dossier/>,
si ce n'est pas le cas des ajustements de configuration
peuvent être nécessaires.

La configuration Apache doit contenir quelque chose comme ça :

```apacheconf
SetEnv MW_INSTALL_PATH /chemin/vers/le/dossier/du/projet/
```

Aller sur <http://localhost/sous-dossier/core/mw-config/>
et suivre les étapes jusqu'à la fin de l'installation.
À la fin, ne pas télécharger le fichier `LocalSettings.php`
et à la place créer un fichier `dbconfig.php` avec ces informations
(à adapter) :

```php
<?php
$wgServer = "http://localhost";
$wgDBtype = "mysql";
$wgDBserver = "localhost";
$wgDBname = "base_sql";
$wgDBuser = "utilisateur_sql";
$wgDBpassword = "motdepasse_sql";
$wgScriptPath = "/sous-dossier";
```

Si le site est accessible via un sous-dossier,
il peut être nécessaire de modifier le fichier `.htaccess`
comme ceci (ne pas commiter ce changement) :

```diff
diff --git a/.htaccess b/.htaccess
index 129b89d..2b17f4e 100644
--- a/.htaccess
+++ b/.htaccess
@@ -3,7 +3,7 @@ RewriteEngine on
 RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} !-f
 RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} !-d
 RewriteCond %{REQUEST_URI} !^/rest.php
-RewriteRule ^(.*)$ %{DOCUMENT_ROOT}/index.php [L]
+RewriteRule ^(.*)$ %{DOCUMENT_ROOT}/sous-dossier/index.php [L]
 
 RedirectMatch 403 /\.git

```

Il faut ensuite lancer cette commande afin
d'initialiser la base de données
de Semantic MediaWiki :

```shell
core/maintenance/update.php --conf LocalSettings.php
```

Le site devrait maintenant être accessible sur <http://localhost/sous-dossier/>.
Il est par contre vide et sans les nombreux modèles
et attributs SMW utilisés en prod ;
il peut donc être plus utile d'importer un export
de la base de données de prod dans la BDD locale.
