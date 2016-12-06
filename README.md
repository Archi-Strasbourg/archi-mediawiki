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
```

You then need to run:

```bash
composer updatedb
```

If you get some SQL errors, try to run `composer updatedb` before reporting an issue.
