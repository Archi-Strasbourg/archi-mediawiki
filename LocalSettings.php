<?php
require_once __DIR__.'/dbconfig.php';

$wgSitename = "Archi-Wiki";
$wgScriptExtension = ".php";
$wgStylePath = "$wgScriptPath/skins";
$wgResourceBasePath = $wgScriptPath;
$wgLogo = "$wgResourceBasePath/logo_archi_wiki.png";
$wgFavicon = "$wgResourceBasePath/favicon.png";
$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmergencyContact = "contact@rudloff.pro";
$wgPasswordSender = "contact@rudloff.pro";
$wgEnotifUserTalk = true;
$wgEnotifWatchlist = true;
$wgEmailAuthentication = true;
$wgDBprefix = "";
$wgDBTableOptions = "ENGINE=InnoDB, DEFAULT CHARSET=binary";
$wgDBmysql5 = false;
$wgMainCacheType = CACHE_NONE;
$wgMemCachedServers = array();
$wgEnableUploads = true;
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = "/usr/bin/convert";
$wgUseInstantCommons = true;
$wgShellLocale = "fr_FR.utf8";
$wgLanguageCode = "fr";
$wgRightsPage = "";
$wgRightsUrl = "";
$wgRightsText = "";
$wgRightsIcon = "";
$wgDiff3 = "/usr/bin/diff3";
$wgDefaultSkin = "vector";

//Extensions
wfLoadSkin('Vector');
enableSemantics('localhost');
wfLoadExtension('ParserFunctions');
wfLoadExtension('Cite');
wfLoadExtension('Comments');
wfLoadExtension('CommonsMetadata');
wfLoadExtension('NewsTab');
wfLoadExtension('CategoryBreadcrumb');
wfLoadExtension('ArchiMaps');
wfLoadExtension('VisualEditor');
wfLoadExtension('TemplateData');
require_once "$IP/extensions/Arrays/Arrays.php";
require_once "$IP/extensions/MultimediaViewer/MultimediaViewer.php";

//VisualEditor
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgVirtualRestConfig['modules']['parsoid'] = array(
    'url' => 'http://localhost:8142',
    'prefix' => 'localhost'
);

$egMapsEnableCategory = false;
$wgAllowCopyUploads = true;
$wgShowExceptionDetails = true;

//Namespaces
define("NS_ADDRESS", 4000);
define("NS_ADDRESS_NEWS", 4001);
$wgExtraNamespaces[NS_ADDRESS] = "Adresse";
$wgExtraNamespaces[NS_ADDRESS_NEWS] = "Actualités_adresse";
define("NS_SOURCE", 4002);
define("NS_SOURCE_TALK", 4003);
$wgExtraNamespaces[NS_SOURCE] = "Source";
$wgExtraNamespaces[NS_SOURCE_TALK] = "Discussion_source";

$wgNamespacesWithSubpages[NS_ADDRESS] = true;
$wgVisualEditorAvailableNamespaces[NS_ADDRESS] = true;
$smwgNamespacesWithSemanticLinks[NS_ADDRESS] = true;

//À retirer en production
$wgGroupPermissions['*']['bot'] = true;
$wgGroupPermissions['*']['upload_by_url'] = true;
