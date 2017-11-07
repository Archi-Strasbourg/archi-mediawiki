<?php

require_once __DIR__.'/dbconfig.php';
require_once __DIR__.'/namespaces.php';

$wgSitename = 'Archi-Wiki';
$wgScriptExtension = '.php';
$wgStylePath = "$wgScriptPath/skins";
$wgResourceBasePath = $wgScriptPath;
$wgScript = $wgScriptPath.'/';
$wgLogo = "$wgResourceBasePath/logo_archi_wiki.png";
$wgFavicon = "$wgResourceBasePath/favicon.png";
$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmergencyContact = 'contact@archi-strasbourg.org';
$wgPasswordSender = 'contact@archi-strasbourg.org';
$wgEnotifUserTalk = true;
$wgEnotifWatchlist = true;
$wgEmailAuthentication = true;
$wgAllowHTMLEmail = true;
$wgUserEmailUseReplyTo = true;
$wgDBprefix = '';
$wgDBTableOptions = 'ENGINE=InnoDB, DEFAULT CHARSET=binary';
$wgDBmysql5 = false;
$wgEnableUploads = true;
$wgUseImageMagick = true;
$wgImageMagickConvertCommand = '/usr/bin/convert';
$wgUseInstantCommons = true;
$wgShellLocale = 'fr_FR.utf8';
$wgLanguageCode = 'fr';
$wgRightsPage = '';
$wgRightsUrl = '';
$wgRightsText = '';
$wgRightsIcon = '';
$wgDiff3 = '/usr/bin/diff3';
$wgDefaultSkin = 'ArchiWiki';
$wgAllowSlowParserFunctions = true;
$wgPFEnableStringFunctions = true;
$wgExternalLinkTarget = '_blank';
$wgEmailConfirmToEdit = true;
$wgPasswordAttemptThrottle = false;
$wgCategoryCollation = 'numeric';

setlocale(LC_TIME, 'fr_FR');

//Permissions
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['user']['edit'] = true;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['user']['createpage'] = true;

//Extensions
wfLoadSkin('archi-wiki');
wfLoadSkin('Vector');
enableSemantics('localhost');
wfLoadExtension('ParserFunctions');
wfLoadExtension('Cite');
wfLoadExtension('Comments');
wfLoadExtension('CommonsMetadata');
wfLoadExtension('ArchiNewsTab');
wfLoadExtension('CategoryBreadcrumb');
wfLoadExtension('ArchiMaps');
wfLoadExtension('VisualEditor');
wfLoadExtension('TemplateData');
wfLoadExtension('ArchiHome');
wfLoadExtension('ArchiBlog');
wfLoadExtension('ArchiComments');
wfLoadExtension('ArchiFooter');
wfLoadExtension('Newsletter');
wfLoadExtension('Nuke');
wfLoadExtension('EmailuserHtml');
wfLoadExtension('SectionsCount');
wfLoadExtension('UniversalLanguageSelector');
wfLoadExtension('CleanChanges');
wfLoadExtension('LanguageCode');
wfLoadExtension('LinkToArchive');
wfLoadExtensions(['ConfirmEdit', 'ConfirmEdit/ReCaptchaNoCaptcha']);
wfLoadExtension('PageForms');
wfLoadExtension('SemanticFormsSelect');
wfLoadExtension('EmailLogin');
wfLoadExtension('ReplaceText');
wfLoadExtension('UserMerge');
wfLoadExtension('ContactPage');
wfLoadExtension('Elastica');
require_once "$IP/extensions/Arrays/Arrays.php";
require_once "$IP/extensions/MultimediaViewer/MultimediaViewer.php";
require_once "$IP/extensions/UploadWizard/UploadWizard.php";
require_once "$IP/extensions/AddThis/AddThis.php";
require_once "$IP/extensions/TextExtracts/TextExtracts.php";
require_once "$IP/extensions/Echo/Echo.php";
require_once "$IP/extensions/Variables/Variables.php";
require_once "$IP/extensions/Loops/Loops.php";
require_once "$IP/extensions/Paypal/Paypal.php";
require_once "$IP/extensions/Translate/Translate.php";
require_once "$IP/extensions/DynamicPageList/DynamicPageList.php";
require_once "$IP/extensions/NukeDPL/NukeDPL.php";
require_once "$IP/extensions/HideNamespace/HideNamespace.php";
require_once "$IP/extensions/GoogleCustomWikiSearch/GoogleCustomWikiSearch.php";
require_once "$IP/extensions/CirrusSearch/CirrusSearch.php";

include_once __DIR__.'/apikeys.php';

//VisualEditor
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgVirtualRestConfig['modules']['parsoid'] = [
    'url'    => 'http://localhost:8142',
    'prefix' => 'localhost',
];
$wgVisualEditorSupportedSkins = ['vector', 'archiwiki'];

//UploadWizard
$wgExtensionFunctions[] = function () {
    $GLOBALS['wgUploadNavigationUrl'] = SpecialPage::getTitleFor('UploadWizard')->getLocalURL();

    return true;
};
$wgUploadWizardConfig['tutorial']['skip'] = true;
$wgUploadWizardConfig['uwLanguages'] = [
    'fr' => 'Français',
    'de' => 'Deutsch',
    'en' => 'English',
];
$wgFileExtensions[] = 'pdf';

//ReCaptcha
$wgCaptchaClass = 'ReCaptchaNoCaptcha';
$wgCaptchaTriggers['contactpage'] = true;

//Footer
$wgHooks['SkinTemplateOutputPageBeforeExec'][] = function ($sk, &$tpl) {
    $tpl->data['footerlinks']['places'] = [];

    $tpl->set(
        'contact',
        Html::element(
            'a',
            ['href' => SpecialPage::getTitleFor('Contact')->getLocalURL()],
            'Nous contacter'
        )
    );
    $tpl->data['footerlinks']['places'][] = 'contact';

    $tpl->set(
        'faq',
        Html::element(
            'a',
            ['href' => Title::newFromText('Foire aux questions')->getLocalURL()],
            'Foire aux questions'
        )
    );
    $tpl->data['footerlinks']['places'][] = 'faq';

    $tpl->set(
        'opendata',
        Html::element(
            'a',
            ['href' => Title::newFromText('Open Data')->getLocalURL()],
            'Open Data'
        )
    );
    $tpl->data['footerlinks']['places'][] = 'opendata';

    $tpl->set(
        'legal',
        Html::element(
            'a',
            ['href' => Title::newFromText('Mentions légales')->getLocalURL()],
            'Mentions légales'
        )
    );
    $tpl->data['footerlinks']['places'][] = 'legal';

    return true;
};

//ContactPage
$wgContactConfig['default'] = [
    'RecipientUser'    => 'Digito',
    'RequireDetails'   => true,
    'AdditionalFields' => [],
    'IncludeIP'        => false,
    'DisplayFormat'    => 'table',
    'RLModules'        => [],
    'RLStyleModules'   => [],
    'AdditionalFields' => [
        'Text' => [
            'label-message' => 'emailmessage',
            'type'          => 'textarea',
            'required'      => true,
        ],
    ],
    'SenderEmail' => 'contact@archi-strasbourg.org',
    'SenderName'  => 'Archi-Wiki',
];
$wgContactConfig['membership'] = [
    'RecipientUser'    => 'Digito',
    'RequireDetails'   => true,
    'AdditionalFields' => [],
    'IncludeIP'        => false,
    'DisplayFormat'    => 'table',
    'RLModules'        => [],
    'RLStyleModules'   => [],
    'AdditionalFields' => [
        'job' => [
            'label'         => 'Profession/société :',
            'type'          => 'text',
            'required'      => false,
        ],
        'address' => [
            'label'         => 'Adresse postale :',
            'type'          => 'text',
            'required'      => false,
        ],
        'tel' => [
            'label'         => 'Numéro de téléphone :',
            'type'          => 'text',
            'required'      => false,
        ],
        'Text' => [
            'label'         => 'Laisser un commentaire :',
            'type'          => 'textarea',
            'required'      => false,
            'rows'          => 5,
        ],
        'amount' => [
            'label'         => 'Cotisation :',
            'type'          => 'radio',
            'options'       => [
                '<b>10 €</b><br/>Tarif réduit pour étudiants, bénéficiaires du RSA'.
                    'et personnes non-imposables, sur justificatif'                                        => 10,
                '<b>20 €</b></br>Particulier'                                                              => 20,
                '<b>30 €</b></br>Couple, famille'                                                          => 30,
                '<b>50 €</b></br>Vous recevrez un reçu fiscal, votre don ne vous coûtera que 30,20 euros.' => 50,
                '<b>80 €</b></br>Vous recevrez un reçu fiscal, votre don ne vous coûtera que 40,40 euros.'.
                '<br/>Si vous le souhaitez, vous pourrez figurer sur notre liste de donateurs<br/>'.
                    'et pour une entreprise faire apparaître votre logo et un lien '.
                    'sur le site de votre société.'=> 80,
            ],
            'required'      => true,
        ],
    ],
    'SenderEmail' => 'contact@archi-strasbourg.org',
    'SenderName'  => 'Archi-Wiki',
];

//AddThis
$wgAddThisHeader = false;
$wgResourceModules['ext.addThis'] = [
    'position' => 'top',
    'styles'   => 'addThis.css',
];

$egMapsEnableCategory = false;
$wgAllowCopyUploads = true;
$wgShowExceptionDetails = true;

//Categories
$wgCountryCategory = 'Pays';
$wgShowBreadcrumbCategories = [$wgCountryCategory];
$wgHiddenCategories = [$wgCountryCategory];

//Namespaces
$wgExtraNamespaces[NS_ADDRESS] = 'Adresse';
$wgExtraNamespaces[NS_ADDRESS_TALK] = 'Discussion_adresse';
$wgExtraNamespaces[NS_ADDRESS_NEWS] = 'Actualités_adresse';
$wgExtraNamespaces[NS_SOURCE] = 'Source';
$wgExtraNamespaces[NS_SOURCE_TALK] = 'Discussion_source';
$wgExtraNamespaces[NS_NEWS] = 'Actualité';
$wgExtraNamespaces[NS_NEWS_TALK] = 'Discussion_actualité';
$wgExtraNamespaces[NS_PERSON] = 'Personne';
$wgExtraNamespaces[NS_PERSON_TALK] = 'Discussion_personne';
$wgExtraNamespaces[NS_ROUTE] = 'Parcours';

$wgNamespacesWithSubpages[NS_ADDRESS] = true;
$wgNamespacesWithSubpages[NS_ADDRESS_NEWS] = true;
$wgNamespacesWithSubpages[NS_PERSON] = true;
$wgNamespacesWithSubpages[NS_SOURCE] = true;
$wgVisualEditorAvailableNamespaces[NS_ADDRESS] = true;
$wgVisualEditorAvailableNamespaces[NS_ADDRESS_NEWS] = true;
$wgVisualEditorAvailableNamespaces[NS_PERSON] = true;
$wgVisualEditorAvailableNamespaces[NS_SOURCE] = true;
$wgVisualEditorAvailableNamespaces[NS_MEDIAWIKI] = true;
$smwgNamespacesWithSemanticLinks[NS_ADDRESS] = true;
$smwgNamespacesWithSemanticLinks[NS_ADDRESS_NEWS] = true;
$smwgNamespacesWithSemanticLinks[NS_PERSON] = true;
$smwgNamespacesWithSemanticLinks[NS_USER] = true;
$smwgNamespacesWithSemanticLinks[NS_SOURCE] = true;
$smwgNamespacesWithSemanticLinks[NS_NEWS] = true;
$wgNamespacesToBeSearchedDefault[NS_ADDRESS] = true;
$wgNamespacesToBeSearchedDefault[NS_PERSON] = true;

//Cache
$wgMainCacheType = CACHE_ACCEL;
$wgSessionCacheType = CACHE_DB;

//Semantic forms
$srfgFormats[] = 'map';

//Comments
$wgCommentsSortDescending = true;
$wgGroupPermissions['*']['comment'] = false;
$wgGroupPermissions['user']['comment'] = true;

//Translate
$wgEnablePageTranslation = true;
$wgTranslatePageTranslationULS = true;
$wgGroupPermissions['user']['translate'] = true;
$wgGroupPermissions['user']['translate-messagereview'] = true;
$wgGroupPermissions['user']['translate-groupreview'] = true;
$wgGroupPermissions['user']['translate-import'] = true;
$wgGroupPermissions['sysop']['pagetranslation'] = true;
$wgGroupPermissions['sysop']['translate-manage'] = true;
$wgCCTrailerFilter = true;
$wgULSIMEEnabled = false;

//HideNamespace
$wgHidensNamespaces = [NS_ADDRESS];

//Gallery
$wgGalleryOptions['imageWidth'] = 180;
$wgGalleryOptions['imageHeight'] = 240;

//Google Search
$wgGoogleCustomWikiSearchAppendToSearch = true;
$wgGoogleCustomWikiSearchCodeVersion = 1;

//SectionsCount
$wgSectionsCountIgnoreSections = ['Références'];

//SMW
$smwgQDefaultLimit = 500;

//UserMerge
$wgGroupPermissions['bureaucrat']['usermerge'] = true;

//CirrusSearch
$wgSearchType = 'CirrusSearch';
$wgCirrusSearchServers = ['localhost'];

//Permissions requises pour aw2mw
$wgGroupPermissions['bot']['bot'] = true;
$wgGroupPermissions['bot']['upload_by_url'] = true;
$wgGroupPermissions['bot']['noratelimit'] = true;
