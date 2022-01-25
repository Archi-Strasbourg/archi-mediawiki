<?php

require_once __DIR__ . '/dbconfig.php';
require_once __DIR__ . '/namespaces.php';

/** @var $wgScriptPath string */

$wgSitename = 'Archi-Wiki';
$wgScriptExtension = '.php';
$wgStylePath = "$wgScriptPath/skins";
$wgResourceBasePath = $wgScriptPath;
$wgScript = $wgScriptPath . '/';
$wgLogo = "$wgResourceBasePath/logo_archi_wiki.png";
$wgFavicon = "$wgResourceBasePath/favicon.png";
$wgEnableEmail = true;
$wgEnableUserEmail = true;
$wgEmergencyContact = 'contact@archi-wiki.org';
$wgPasswordSender = 'contact@archi-wiki.org';
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
$wgMaxArticleSize = 4096;

setlocale(LC_TIME, 'fr_FR');

//Permissions
$wgGroupPermissions['*']['edit'] = false;
$wgGroupPermissions['user']['edit'] = true;
$wgGroupPermissions['*']['createpage'] = false;
$wgGroupPermissions['user']['createpage'] = true;

//Extensions
wfLoadSkin('archi-wiki');
wfLoadSkin('Vector');
if (function_exists('enableSemantics')) {
    enableSemantics('localhost');
}
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
wfLoadExtensions(['ConfirmEdit', 'ConfirmEdit/QuestyCaptcha']);
wfLoadExtension('PageForms');
wfLoadExtension('SemanticFormsSelect');
wfLoadExtension('EmailLogin');
wfLoadExtension('ReplaceText');
wfLoadExtension('UserMerge');
wfLoadExtension('ContactPage');
wfLoadExtension('Elastica');
wfLoadExtension('ArchiMove');
wfLoadExtension('LookupUser');
wfLoadExtension('PageImages');
wfLoadExtension('MobileFrontend');
wfLoadExtension('GeoData');
wfLoadExtension('ArchiTweaks');
wfLoadExtension('BlockAndNuke');
wfLoadExtension('DismissableSiteNotice');
wfLoadExtension('Arrays');
wfLoadExtension('MultimediaViewer');
wfLoadExtension('UploadWizard');
wfLoadExtension('TextExtracts');
wfLoadExtension('Echo');
wfLoadExtension('Variables');
wfLoadExtension('Loops');
wfLoadExtension('CirrusSearch');
wfLoadExtension('MixedNamespaceSearchSuggestions');
wfLoadExtension('Translate');
wfLoadExtension('Maps');
wfLoadExtension('WikiEditor');
wfLoadExtension('SemanticResultFormats');
wfLoadExtension('Flow');
wfLoadExtension('Thanks');
wfLoadExtension('SmiteSpam');

/** @var $IP string */
require_once "$IP/extensions/HideNamespace/HideNamespace.php";
require_once "$IP/extensions/GoogleCustomWikiSearch/GoogleCustomWikiSearch.php";
require_once "$IP/extensions/ContributionScores/ContributionScores.php";

include_once __DIR__ . '/apikeys.php';

//VisualEditor
$wgDefaultUserOptions['visualeditor-enable'] = 1;
$wgVisualEditorSupportedSkins = ['vector', 'archiwiki'];
$wgUploadDialog = [
    'fields' => [
        'description' => true,
        'date' => true,
    ],
    'licensemessages' => [
        'local' => 'generic-local',
        'foreign' => 'generic-foreign',
    ],
    'comment' => 'Import depuis l\'éditeur visuel sur la page $PAGENAME',
    'format' => [
        'filepage' => '{{Infobox image
|description=$DESCRIPTION
|date=$DATE
|auteur=$AUTHOR
|licence =$LICENSE
|tags =
|source=$SOURCE
}}',
        'description' => '$TEXT',
        'ownwork' => 'Travail personnel',
        'license' => '{{Modèle:CC-BY-SA}}',
    ],
];

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
$wgFileExtensions[] = 'doc';
$wgFileExtensions[] = 'docx';

// Captcha
$wgCaptchaQuestions = [
    "Quel est la couleur du logo d'Archi-Wiki ?" => 'noir',
    'Où se trouve le siège du Parlement Européen ?' => 'Strasbourg'
];
$wgCaptchaTriggers['contactpage'] = true;
$wgRateLimits['badcaptcha']['ip'] = ['3', '60'];

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
    'RecipientUser' => 'Archi-Wiki',
    'RequireDetails' => true,
    'IncludeIP' => false,
    'DisplayFormat' => 'table',
    'RLModules' => [],
    'RLStyleModules' => [],
    'AdditionalFields' => [
        'Text' => [
            'label-message' => 'emailmessage',
            'type' => 'textarea',
            'required' => true,
        ],
    ],
    'SenderEmail' => 'contact@archi-wiki.org',
    'SenderName' => 'Archi-Wiki',
];
$wgContactConfig['membership'] = [
    'RecipientUser' => 'Archi-Wiki',
    'RequireDetails' => true,
    'IncludeIP' => false,
    'DisplayFormat' => 'table',
    'RLModules' => [],
    'RLStyleModules' => [],
    'AdditionalFields' => [
        'job' => [
            'label' => 'Profession/société :',
            'type' => 'text',
            'required' => false,
        ],
        'address' => [
            'label' => 'Adresse postale :',
            'type' => 'text',
            'required' => false,
        ],
        'city' => [
            'label' => 'CP/ville :',
            'type' => 'text',
            'required' => false,
        ],
        'tel' => [
            'label' => 'Numéro de téléphone :',
            'type' => 'text',
            'required' => false,
        ],
        'Text' => [
            'label' => 'Laisser un commentaire :',
            'type' => 'textarea',
            'required' => false,
            'rows' => 5,
        ],
        'amount' => [
            'label' => 'Cotisation :',
            'type' => 'radio',
            'options' => [
                '<b>10 €</b><br/>Tarif réduit pour étudiants, bénéficiaires du RSA' .
                'et personnes non-imposables, sur justificatif' => 10,
                '<b>20 €</b></br>Particulier' => 20,
                '<b>30 €</b></br>Couple, famille' => 30,
                '<b>50 €</b></br>Vous recevrez un reçu fiscal, votre don ne vous coûtera que 30,20 euros.' => 50,
                '<b>80 €</b></br>Vous recevrez un reçu fiscal, votre don ne vous coûtera que 40,40 euros.' .
                '<br/>Si vous le souhaitez, vous pourrez figurer sur notre liste de donateurs<br/>' .
                'et pour une entreprise faire apparaître votre logo et un lien ' .
                'sur le site de votre société.' => 80,
            ],
            'required' => true,
        ],
    ],
    'SenderEmail' => 'contact@archi-wiki.org',
    'SenderName' => 'Archi-Wiki',
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
$wgExtraNamespaces[NS_NEWS] = 'Edito';
$wgNamespaceAliases['Actualité'] = NS_NEWS;
$wgExtraNamespaces[NS_NEWS_TALK] = 'Discussion_edito';
$wgNamespaceAliases['Discussion_actualité'] = NS_NEWS_TALK;
$wgExtraNamespaces[NS_PERSON] = 'Personne';
$wgExtraNamespaces[NS_PERSON_TALK] = 'Discussion_personne';
$wgExtraNamespaces[NS_ROUTE] = 'Parcours';
$wgExtraNamespaces[NS_ROUTE_TALK] = 'Discussion_parcours';
$wgExtraNamespaces[NS_BRIEF] = 'Brève';
$wgExtraNamespaces[NS_BRIEF_TALK] = 'Discussion_brève';

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
$smwgNamespacesWithSemanticLinks[NS_BRIEF] = true;
$wgNamespacesToBeSearchedDefault[NS_ADDRESS] = true;
$wgNamespacesToBeSearchedDefault[NS_PERSON] = true;

$wgContentNamespaces[] = NS_ADDRESS;
$wgContentNamespaces[] = NS_PERSON;

$wgNamespaceContentModels[NS_ADDRESS_TALK] = 'flow-board';

$wgArticleRobotPolicies['Adresse:Bac à sable'] = 'noindex';

//Cache
$wgMainCacheType = CACHE_ACCEL;
$wgSessionCacheType = CACHE_DB;
$wgParserCacheType = CACHE_DB;

//Semantic forms
$srfgFormats[] = 'map';

//Comments
$wgCommentsSortDescending = true;
$wgGroupPermissions['*']['comment'] = false;
$wgGroupPermissions['user']['comment'] = true;
$wgGroupPermissions['sysop']['commentadmin'] = true;
$wgCommentsDefaultAvatar = '/assets/default_ml.gif';

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
$wgGoogleCustomWikiSearchCodeVersion = 2;

//SectionsCount
$wgSectionsCountIgnoreSections = ['Références'];

//SMW
$smwgQDefaultLimit = 500;
$smwgQMaxInlineLimit = 20000;
$smwgQMaxLimit = 20000;
$smwgQUpperbound = 20000;
$smwgInlineErrors = false;

//UserMerge
$wgGroupPermissions['bureaucrat']['usermerge'] = true;

//CirrusSearch
$wgSearchType = 'CirrusSearch';
$wgCirrusSearchServers = ['localhost'];

//LookupUser
$wgGroupPermissions['bureaucrat']['lookupuser'] = true;

//PageImages
$wgPageImagesNamespaces[] = NS_ADDRESS;
$wgPageImagesNamespaces[] = NS_NEWS;

//Loops
$egLoopsCounterLimit = 4000;

//Permissions requises pour aw2mw
$wgGroupPermissions['bot']['bot'] = true;
$wgGroupPermissions['bot']['upload_by_url'] = true;
$wgGroupPermissions['bot']['noratelimit'] = true;

//MetaDescriptionTag
wfLoadExtension('MetaDescriptionTag');

//MobileFrontend
$wgMFNearby = true;
$wgMFContentNamespace = NS_ADDRESS;
$wgMFQueryPropModules = ['archiDescription'];
$wgMFCollapseSectionsByDefault = false;

// BlockAndNuke
$wgWhitelist = $IP . '/whitelist.txt';
$wgBaNnomerge = true;

// DismissableSiteNotice
$wgDismissableSiteNoticeForAnons = true;

// ContributionScores
$wgContribScoreIgnoreBots = true;

// Maps
$egMapsGeoCacheTtl = BagOStuff::TTL_MONTH;
$egMapsEnableCoordinateFunction = false;
$egMapsGeoCacheType = CACHE_DB;
