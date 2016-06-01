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
$wgAllowSlowParserFunctions = true;

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
wfLoadExtension('ArchiHome');
wfLoadExtension('ArchiBlog');
wfLoadExtensions(array('ConfirmEdit', 'ConfirmEdit/ReCaptchaNoCaptcha'));
require_once "$IP/extensions/Arrays/Arrays.php";
require_once "$IP/extensions/MultimediaViewer/MultimediaViewer.php";
require_once "$IP/extensions/UploadWizard/UploadWizard.php";
require_once "$IP/extensions/ContactPage/ContactPage.php";
require_once "$IP/extensions/AddThis/AddThis.php";
require_once "$IP/extensions/TextExtracts/TextExtracts.php";

//VisualEditor
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgVirtualRestConfig['modules']['parsoid'] = array(
    'url' => 'http://localhost:8142',
    'prefix' => 'localhost'
);

//UploadWizard
$wgExtensionFunctions[] = function () {
    $GLOBALS['wgUploadNavigationUrl'] = SpecialPage::getTitleFor('UploadWizard')->getLocalURL();
    return true;
};
$wgUploadWizardConfig['tutorial']['skip'] = true;

//ReCaptcha
$wgCaptchaClass = 'ReCaptchaNoCaptcha';
//$wgCaptchaTriggers['contactpage'] = true;
//À retirer en production
$wgGroupPermissions['user']['skipcaptcha'] = true;

//Footer
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = function ($sk, &$tpl) {
    $tpl->data['footerlinks']['places'] = array();
    $contactLink = Html::element(
        'a',
        array( 'href' => SpecialPage::getTitleFor('Contact')->getLocalURL() ),
        'Nous contacter'
    );
    $tpl->set('contact', $contactLink);
    $tpl->data['footerlinks']['places'][] = 'contact';

    $faq = Html::element(
        'a',
        array( 'href' => Title::newFromText('Foire aux questions')->getLocalURL() ),
        'Foire aux questions'
    );
    $tpl->set('faq', $faq);
    $tpl->data['footerlinks']['places'][] = 'faq';

    $legal = Html::element(
        'a',
        array( 'href' => Title::newFromText('Mentions légales')->getLocalURL() ),
        'Mentions légales'
    );
    $tpl->set('legal', $legal);
    $tpl->data['footerlinks']['places'][] = 'legal';

    return true;
};

//ContactPage
$wgContactConfig['default'] = array(
    'RecipientUser'=>'Rudloff',
    'RequireDetails'=>true,
    'AdditionalFields'=>array(),
    'IncludeIP'=>false,
    'DisplayFormat'=>'table',
    'RLModules' => array(),
    'RLStyleModules' => array(),
    'AdditionalFields' => array(
        'Text' => array(
            'label-message' => 'emailmessage',
            'type' => 'textarea',
            'required' => true
        )
    ),
    'SenderEmail'=>'contact@archi-strasbourg.org',
    'SenderName'=>'Archi-Wiki'
);

//AddThis
$wgAddThisHeader = false;
$wgResourceModules['ext.addThis'] = array(
    'position' => 'top',
    'styles' => 'addThis.css'
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
define("NS_NEWS", 4004);
define("NS_NEWS_TALK", 4005);
$wgExtraNamespaces[NS_NEWS] = "Actualité";
$wgExtraNamespaces[NS_NEWS_TALK] = "Discussion_actualité";

$wgNamespacesWithSubpages[NS_ADDRESS] = true;
$wgVisualEditorAvailableNamespaces[NS_ADDRESS] = true;
$smwgNamespacesWithSemanticLinks[NS_ADDRESS] = true;
$wgNamespacesToBeSearchedDefault[NS_ADDRESS] = true;

//À retirer en production
$wgGroupPermissions['*']['bot'] = true;
$wgGroupPermissions['*']['upload_by_url'] = true;
