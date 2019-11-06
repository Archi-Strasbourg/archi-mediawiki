<?php

namespace ArchiTweaks;

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
     * @throws \MWException
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
                    'content' => '{{Infobox quartier}}' . PHP_EOL .
                        '[[Catégorie:' . $titleText . ']]'
                ];

                // Sous-quartier
                $toCreate[] = [
                    'title' => 'Catégorie:Autre (autre) (' . $titleText . ')',
                    'content' => '{{Infobox quartier}}' . PHP_EOL .
                        '[[Catégorie:autre (' . $titleText . ')]]'
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
}