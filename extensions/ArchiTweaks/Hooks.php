<?php

namespace ArchiTweaks;

use MailAddress;
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
     * @param $address
     * @param MailAddress $from
     */
    public static function onEmailUser(&$address, MailAddress &$from)
    {
        global $wgPasswordSender;

        // Pour Mailjet, il faut que l'expéditeur soit une adresse validée.
        $from->address = $wgPasswordSender;
    }

    /**
     * @param $contactRecipientAddress
     * @param $replyTo
     * @param $subject
     * @param $text
     * @param $formType
     * @param $formData
     */
    public static function onContactForm(&$contactRecipientAddress, &$replyTo, &$subject, &$text, $formType, $formData)
    {
        // Comme on a modifié l'expéditeur, on met le vrai expéditeur en adresse de réponse.
        $replyTo = new MailAddress($formData['FromAddress'], $formData['FromName']);
    }
}
