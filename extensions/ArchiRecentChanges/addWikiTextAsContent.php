<?php
namespace ArchiRecentChanges;

use ApiMain;
use Article;
use CategoryBreadcrumb\CategoryBreadcrumb;
use ContentHandler;
use DateTime;
use DerivativeContext;
use DerivativeRequest;
use Exception;
use Linker;
use MediaWiki\MediaWikiServices;
use MediaWiki\Revision\RevisionRecord;
use MediaWiki\Revision\SlotRecord;
use MobileContext;
use MWException;
use ObjectCache;
use RequestContext;
use SMW\Services\ServicesFactory as ApplicationFactory;
use SpecialPage;
use TextExtracts\TextTruncator;
use Title;
use TextExtracts\ExtractFormatter;
use ConfigException;
use User;

$aResult=array();
$text = $_POST['text'];
global $wgOut;
$context = new DerivativeContext( $wgOut->getContext() );
$context->setTitle( Title::newFromText( "tmp" ) );
$output = $context->getOutput();
$output->addWikiTextAsContent($text);
$aResult['result'] = $output->getHTML();



echo json_encode($aResult);