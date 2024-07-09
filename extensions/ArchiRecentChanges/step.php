<?php
// Check if the rccontinue POST value is set
$result=array();

use DateTime;
use MediaWiki\MediaWikiServices;
use Title;

if (isset($_POST['rccontinue'])) {
    try{
        $rccontinue = $_POST['rccontinue'];


        $output= $this->getOutput();
        
        $addresses = $this->apiRequest(
            [
                'action' => 'query',
                'list' => 'recentchanges',
                'rcnamespace' => NS_ADDRESS . '|' . NS_PERSON,
                'rctoponly' => true,
                'rcshow' => '!redirect',
                'rclimit' => 20,
                'rccontinue' => $rccontinue
            ]
        );
        $news = $this->apiRequest(
            [
                'action' => 'query',
                'list' => 'recentchanges',
                'rcnamespace' => NS_ADDRESS_NEWS,
                'rctoponly' => true,
                'rcshow' => '!redirect',
                'rclimit' => 20,
                'rccontinue' => $rccontinue
            ]
        );
        foreach ($addresses['query']['recentchanges'] as &$address) {
            foreach ($news['query']['recentchanges'] as &$article) {
                if (isset($address['title']) && isset($article['title'])) {
                    $addressTitle = Title::newFromText($address['title']);
                    $articleTitle = Title::newFromText($article['title']);
                    if ($addressTitle->getText() == $articleTitle->getText()) {
                        $revisionLookup = MediaWikiServices::getInstance()->getRevisionLookup();
                        $addressRev = $revisionLookup->getRevisionById($addressTitle->getLatestRevID());
                        $articleRev = $revisionLookup->getRevisionById($articleTitle->getLatestRevID());
                        if ($articleRev->getTimestamp() > $addressRev->getTimestamp()) {
                            $parent = $address;
                            $address = $article;
                            $address['parent'] = $parent;
                        }
                    }
                }
            }
        }
        unset($addresses['query']['recentchanges']['_element']);
        usort($addresses['query']['recentchanges'], [$this, 'sortChanges']);
        $i = 0;
        foreach ($addresses['query']['recentchanges'] as $change) {
            if ($i >= 20) {
                break;
            }
            if (isset($change['title']) && $change['title'] != 'Adresse:Bac à sable') {
                $title = Title::newFromText($change['title']);
                //Il faudra peut être utiliser $title->getPageLanguage()->getCode() quand Translate sera activé
                $titleLanguageCode = $title->getSubpageText();
                if ($titleLanguageCode == $title->getBaseText()) {
                    $titleLanguageCode = 'fr';
                }
                if ($titleLanguageCode == $this->languageCode) {
                    $i++;
                    $id = $title->getArticleID();
                    if (isset($change['parent'])) {
                        $mainTitle = Title::newFromText($change['parent']['title']);
                    } else {
                        $mainTitle = $title;
                    }
                    $revision = MediaWikiServices::getInstance()->getRevisionLookup()->getRevisionByTitle($title);
                    preg_match('#/\*(.*)\*/#', $revision->getComment()->text, $matches);
                    if (isset($matches[1])) {
                        $sectionName = str_replace(
                            '[', '<sup>',
                            str_replace(
                                ']', '</sup>',
                                trim($matches[1])
                            )
                        );
                    }
                    $extract = null;
                    $sectionNumber = null;
                    // On essaie d'avoir un extrait de la section modifiée.
                    if (!empty($sectionName)) {
                        $sections = $this->apiRequest(
                            [
                                'action' => 'parse',
                                'page' => $change['title'],
                                'prop' => 'sections',
                            ]
                        );
                        foreach ($sections['parse']['sections'] as $section) {
                            if (isset($section['line']) && $section['line'] == $sectionName) {
                                $sectionNumber = $section['index'];
                            }
                        }
                        if (isset($sectionNumber)) {
                            $extract = $this->getExtract($title, $sectionNumber);
                        }
                    }
                    // Sinon on prend le début de l'article.
                    if (!isset($extract)) {
                        $extracts = $this->apiRequest(
                            [
                                'action' => 'query',
                                'prop' => 'extracts',
                                'titles' => $change['title'],
                                'explaintext' => true,
                                'exchars' => 120,
                                'exsectionformat' => 'plain',
                            ]
                        );
                        $extract = $extracts['query']['pages'][$id]['extract']['*'];
                    }
                    $properties = $this->apiRequest(
                        [
                            'action' => 'ask',
                            'query' => '[[' . $mainTitle . ']]|?Image principale|?Adresse complète',
                        ]
                    );
                    $output->addHTML('<article class="latest-changes-recent-change-container batch1">');
                    $output->addHTML('<article class="latest-changes-recent-change">');
                    $wikitext = '=== ' . preg_replace('/\(.*\)/', '', $title->getBaseText()) . ' ===' . PHP_EOL;
                    $output->addWikiTextAsContent($wikitext);
                    if (isset($properties['query']['results'][(string)$mainTitle]) && !empty($properties['query']['results'][(string)$mainTitle]['printouts']['Adresse complète'])) {
                        $output->addWikiTextAsContent($properties['query']['results'][(string)$mainTitle]['printouts']['Adresse complète'][0]['fulltext']);
                    }
                    $output->addHTML($this->getCategoryTree($mainTitle));
                    if (isset($properties['query']['results'][(string)$mainTitle]) && !empty($properties['query']['results'][(string)$mainTitle]['printouts']['Image principale'])) {
                        $output->addWikiTextAsContent('[[' . $properties['query']['results'][(string)$mainTitle]['printouts']['Image principale'][0]['fulltext'] .
                            '|thumb|left|100px]]');
                    }
                    $date = new DateTime($change['timestamp']);
                    $output->addWikiTextAsContent("''" . $date->format('d/m/Y') . "''");
                    $output->addHTML('<p>' . $extract . '</p>');
                    $wikitext = '[[' . $title->getFullText() . '|' . wfMessage('readthis')->parse() . ']]';
                    $wikitext = str_replace("\t\t\n", '', $wikitext);
                    $output->addWikiTextAsInterface($wikitext);
                    $output->addHTML('<div style="clear:both;"></div></article></article>');
                }
            }
        }
        $result['html'] = $output->getHTML();
        $result['rccontinue'] = $addresses['continue']['rccontinue'];
    }catch(Exception $e){
        $result['error'] = $e->getMessage();
    }
    echo json_encode($result);
}
?>