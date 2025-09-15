<?php

namespace ArchiTweaks;

use DOMDocument;
use DOMElement;
use DOMXPath;
use Drupal\Component\Utility\Html;
use MediaWiki\Revision\SlotRecord;
use MWException;
use OutputPage;
use Parser;
use RequestContext;
use Title;
use WikiPage;
use WikitextContent;

/**
 * Class Hooks
 * @package ArchiTweaks
 */
class Hooks
{

    /**
     * @param WikiPage $wikiPage
     * @throws MWException
     * @noinspection PhpUnused
     */
    public static function onPageContentInsertComplete(WikiPage &$wikiPage)
    {
        if (!$wikiPage->isNew()) {
            // On ne veut agir qu'à la création.
            return;
        }

        $title = $wikiPage->getTitle();
        $titleText = $title->getText();
        $user = RequestContext::getMain()->getUser();

        $toCreate = [];
        if ($title->getNamespace() == NS_CATEGORY) {
            $contentText = $wikiPage->getContent()->getTextForSearchIndex();

            // Ville
            if (strpos($contentText, 'Infobox ville') !== false) {
                // Quartier
                $toCreate[] = [
                    'title' => 'Catégorie:Autre (' . $titleText . ')',
                    'content' => '{{Infobox quartier|ville=' . $titleText . '}}'
                ];
            }

            // Quartier
            if (strpos($contentText, 'Infobox quartier') !== false) {
                // Sous-quartier
                $toCreate[] = [
                    'title' => 'Catégorie:Autre (' . $titleText . ')',
                    'content' => '{{Infobox sous-quartier|quartier=' . $titleText . '}}'
                ];
            }
        }

        foreach ($toCreate as $newPageInfo) {
            $newTitle = Title::newFromText($newPageInfo['title']);

            // Si la page n'existe pas encore, on la crée.
            if ($newTitle->getArticleID() == 0) {
                $newPage = new WikiPage($newTitle);
                $pageUpdater = $newPage->newPageUpdater($user);
                $pageUpdater->setContent(SlotRecord::MAIN, new WikitextContent($newPageInfo['content']));
                $pageUpdater->saveRevision(\CommentStoreComment::newUnsavedComment('Page créée automatiquement'));
            }
        }
    }

    /**
     * @param Parser $parser
     * @return void
     * @throws MWException
     */
    public static function onParserFirstCallInit(Parser $parser) {
        $parser->setFunctionHook( 'querycacheformlink', [ QueryCacheFormLink::class, 'run' ] );
        $parser->setFunctionHook( 'subcategories', [ Subcategories::class, 'render' ] );
    }

    /**
     * @param OutputPage $out
     * @return void
     * @noinspection PhpUnused
     */
    public static function onOutputPageParserOutput(OutputPage $out): void {
        if ($out->getTitle()->getFullText() == 'Spécial:Recherche') {
            $doc = Html::load($out->getHTML());

            $xpath = new DOMXPath($doc);

            /** @var DOMElement $node */
            foreach ($xpath->query('//*[@class="mw-search-form-wrapper"]//input[@name="fulltext"]') as $node) {
                // On retire ce paramètre pour permettre de renvoyer directement vers un résultat exact.
                $node->parentNode->removeChild($node);
            }

            $out->clearHTML();
            $out->addHTML(Html::serialize($doc));
        }
    }

}
