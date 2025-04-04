# archi-mediawiki

Port of Archi-Wiki to MediaWiki

[Pré-requis](docs/REQUISITES.md)

[Installation](docs/INSTALL.md)

## Setup

Download or `git clone` this repo in a folder named `archi-mediawiki`

If you have a database ready, you need to create a `dbconfig.php` file with at least this content:

```php
<?php
$wgDBtype = "mysql";
$wgDBserver = "localhost";
$wgDBname = "";
$wgDBuser = "";
$wgDBpassword = "";
$wgScriptPath = "/archi-mediawiki";
?>
```

Also create a file named `apikeys.php` if it doesn't exist.

You then need to run [Composer](https://getcomposer.org/):

```bash
composer install
```

If you started from scratch and your database was empty, you should go through the [classic install](https://www.mediawiki.org/wiki/Manual:Config_script) to create the initial tables.

If you get some SQL errors, try to run `composer updatedb` before reporting an issue.

### URL rewriting

If you are using Apache, in order to enable [short URLs](https://www.mediawiki.org/wiki/Manual:Short_URL/Apache), you will need a `.htaccess` file:

```apache
RewriteEngine on

RewriteBase /path/to/archi-mediawiki/

RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} !-f
RewriteCond %{DOCUMENT_ROOT}%{REQUEST_URI} !-d
RewriteRule ^(.*)$ %{DOCUMENT_ROOT}/path/to/archi-mediawiki/ [L]
```
