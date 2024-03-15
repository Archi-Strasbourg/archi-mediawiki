<?php

namespace ArchiTweaks;

use DOMDocument;
use DOMElement;
use DOMXPath;
use MWException;
use OutputPage;
use Parser;
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
     */
    public static function onPageContentInsertComplete(WikiPage &$wikiPage)
    {
        $title = $wikiPage->getTitle();
        $titleText = $title->getText();

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
                $newPage->doEditContent(
                    new WikitextContent($newPageInfo['content']),
                    'Page créée automatiquement'
                );
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
    public static function onOutputPageParserOutput(OutputPage $out) {
        if ($out->getTitle()->getFullText() == 'Spécial:Recherche') {
            $doc = new DOMDocument();

            // Options adaptées pour charger une portion de HTML et pas un document complet.
            $doc->loadHTML(
                mb_convert_encoding($out->getHTML(), 'HTML-ENTITIES', 'UTF-8'),
                LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
            );

            $xpath = new DOMXPath($doc);

            /** @var DOMElement $node */
            foreach ($xpath->query('//*[@class="mw-search-form-wrapper"]//input[@name="fulltext"]') as $node) {
                // On retire ce paramètre pour permettre de renvoyer directement vers un résultat exact.
                $node->parentNode->removeChild($node);
            }

            $out->clearHTML();
            $out->addHTML($doc->saveHTML());
        }
    }

}
