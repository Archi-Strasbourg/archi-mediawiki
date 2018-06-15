# archi-mediawiki

Port of Archi-Wiki to MediaWiki

## Setup

You need to create a `dbconfig.php` file with at least the following variables:

```php
$wgDBtype = "mysql";
$wgDBserver = "localhost";
$wgDBname = "";
$wgDBuser = "";
$wgDBpassword = "";
$wgScriptPath = "/archi-mediawiki";
```

You then need to run [Composer](https://getcomposer.org/):

```bash
composer install
```

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
