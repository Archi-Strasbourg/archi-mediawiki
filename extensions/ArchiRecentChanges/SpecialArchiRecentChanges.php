<?php
/**
 * SpecialArchiRecentChanges class.
 */

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

/**
 * SpecialPage Special:ArchiRecentChanges that displays the custom homepage.
 */
class SpecialArchiRecentChanges extends SpecialPage
{
    private $languageCode;
    private $length;
    private $sort;
    private $endDate;

    /**
     * SpecialArchiRecentChanges constructor.
     */
    public function __construct()
    {
        parent::__construct('ArchiRecentChanges');
    }

    /**
     * Send a request to the MediaWiki API.
     *
     * @param array $options Request parameters
     *
     * @return array
     */
    private function apiRequest(array $options): array
    {
        $params = new DerivativeRequest(
            $this->getRequest(),
            $options
        );
        $api = new ApiMain($params);

        /** @var MobileContext $mobileContext */
        $mobileContext = MediaWikiServices::getInstance()->getService('MobileFrontend.Context');

        /*
         * MobileFrontendHooks ne détecte pas qu'il est dans une sous-requête d'API
         * et il injecte un JS au début du HTML.
         * Pour éviter ça, on lui indique explicitement le contexte.
         */
        $context = new DerivativeContext($mobileContext->getContext());
        $context->setRequest(new DerivativeRequest($context->getRequest(), $options));
        $mobileContext->setContext($context);

        $api->execute();

        return $api->getResult()->getResultData();
    }

    /**
     * Extract text content from an article.
     *
     * @param string $title Article title
     *
     * @return string
     * @throws MWException
     */
    private function getTextFromArticle(string $title): string
    {
        $title = Title::newFromText($title);
        $revision = MediaWikiServices::getInstance()->getRevisionLookup()->getRevisionById($title->getLatestRevID());
        if (isset($revision)) {
            return ContentHandler::getContentText($revision->getContent(SlotRecord::MAIN, RevisionRecord::RAW));
        } else {
            return '';
        }
    }

    /**
     * Get a category tree from an article.
     *
     * @param Title $title Article title
     *
     * @return string Category tree
     */
    public static function getCategoryTree(Title $title)
    {
        $parenttree = $title->getParentCategoryTree();
        CategoryBreadcrumb::checkParentCategory($parenttree);
        CategoryBreadcrumb::checkTree($parenttree);
        $flatTree = CategoryBreadcrumb::getFlatTree($parenttree);
        $return = '';
        $categories = array_reverse($flatTree);
        if (isset($categories[0])) {
            $catTitle = Title::newFromText($categories[0]);
            $return .= Linker::link($catTitle, htmlspecialchars($catTitle->getText()));
            if (isset($categories[1])) {
                $catTitle = Title::newFromText($categories[1]);
                $return .= ' > ' . Linker::link($catTitle, htmlspecialchars($catTitle->getText()));
            }
        }

        return $return;
    }


    /**
     * @param $a
     * @param $b
     * @return int
     * @throws Exception
     */
    private function sortChangesTime($a, $b)
    {
        $dateA = new DateTime($a['timestamp']);
        $dateB = new DateTime($b['timestamp']);

        if ($dateA == $dateB) {
            return 0;
        }

        return ($dateA > $dateB) ? -1 : 1;
    }

    private function sortChangesSize($a, $b)
    {
        $sizeA = abs(intval($a['newlen']) - intval($a['oldlen']));
        $sizeB = abs(intval($b['newlen']) - intval($b['oldlen']));

        if ($sizeA == $sizeB) {
            return 0;
        }

        return ($sizeA > $sizeB) ? -1 : 1;
    }

    /**
     * @param $text
     * @return string
     * @throws ConfigException
     */
    private function convertText($text): string
    {
        $fmt = new ExtractFormatter(
            $text,
            TRUE,
        );

        $truncator = new TextTruncator(false);

        $text = trim(
            preg_replace(
                "/" . ExtractFormatter::SECTION_MARKER_START . '(\d)' . ExtractFormatter::SECTION_MARKER_END . "(.*?)$/m",
                '',
                $truncator->getFirstChars($fmt->getText(), 120)
            )
        );
        if (!empty($text)) {
            $text .= wfMessage('ellipsis')->inContentLanguage()->text();
        }

        return $text;
    }

    /**
     * @param Title $title
     * @param $section
     * @return mixed|string
     * @throws ConfigException
     */
    private function getExtract(Title $title, $section)
    {
        $cache = ObjectCache::getLocalClusterInstance();

        $id = $title->getArticleID();

        $key = $cache->makeKey('archidescription', $id, $section, $title->getTouched());
        $result = $cache->get($key);

        if (!$result) {
            // On refait manuellement ce que fait TextExtracts pour pouvoir le faire sur la section 1.
            $extracts = $this->apiRequest(
                [
                    'action' => 'parse',
                    'pageid' => $id,
                    'prop' => 'text',
                    'section' => $section,
                ]
            );

            $result = '';
            if (isset($extracts['parse']['text'])) {
                $result = $this->convertText($extracts['parse']['text']);
            }

            $cache->set($key, $result);
        }

        return $result;
    }

    public function findInArray($array, $id)
    {
        foreach ($array as $i => $item) {
            if ($item['pageid'] == $id) {
                return $i;
            }
        }
        return -1;
    }

    /**
     * @throws ConfigException
     * @throws MWException
     * @throws Exception
     */
    public function outputRecentChanges()
    {
        $output = $this->getOutput();
        
        
        $this->endDate = new DateTime();
        $this->endDate->sub(new \DateInterval('P' . $this->length . 'D'));

        $addresses = $this->apiRequest(
            [
                'action' => 'query',
                'list' => 'recentchanges',
                'rcnamespace' => NS_ADDRESS . '|' . NS_PERSON,
                'rcshow' => '!bot',
                'rclimit' => 1000,
                'rcend' => $this->endDate->format('c'),
                'rcprop'      => 'title|timestamp|ids|sizes',
                'rctype' => ($this->sort=='newOnly'?'new':'new|edit')
            ]
        );
        
        // regroupe les modifications par page
        $addresses2=[];
        $addresses2['query']['recentchanges'][0]=$addresses['query']['recentchanges'][0];
        foreach ($addresses['query']['recentchanges'] as $key => $address) {
            if (is_int($key)) {
                $indice = $this->findInArray($addresses2['query']['recentchanges'], $address['pageid']);
                if ($indice == -1) {
                    $addresses2['query']['recentchanges'][] = $address;
                } else {
                    $addresses2['query']['recentchanges'][$indice]['oldlen'] = $address['oldlen'];
                }
            }
        }
        
        usort($addresses2['query']['recentchanges'], [$this, ($this->sort=='biggest'?'sortChangesSize':'sortChangesTime')]);

        $i = 0;
        $output->addHTML('<h3 class="batch" id="batch1">du ' .$this->endDate->format('d/m/Y'). ' au ' .(new DateTime())->format('d/m/Y').' : </h3>');
        foreach ($addresses2['query']['recentchanges'] as $change) {
            

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
                    $output->addWikiTextAsContent("''" . $date->format('d/m/Y H:i') . "''");
                    $taille = intval($change['newlen']) - intval($change['oldlen']);
                    if($change['oldlen']==0){
                        $output->addHTML('<p style="color:green;font-weight:bold;">Nouvelle page !</p>');
                    }
                    $output->addHTML('<p style="font-size:0.9em;margin-bottom:15px;color:'.($taille>0?"green;":"red;").(abs($taille)>1000?"font-weight:bold;":"").'font-style: italic;">' . (abs($taille)) . ($taille>0?" nouveaux caractères":" caractères supprimés").'</p>');
                    $output->addHTML('<p>' . $extract . '</p>');
                    $wikitext = '[[' . $title->getFullText() . '|' . wfMessage('readthis')->parse() . ']]';
                    $wikitext = str_replace("\t\t\n", '', $wikitext);
                    $output->addWikiTextAsInterface($wikitext);
                    $output->addHTML('<div style="clear:both;"></div></article></article>');
                }
            }
        }
        return $addresses;
    }

    /**
     * Set the robot policy.
     *
     * @return string
     */
    protected function getRobotPolicy()
    {
        return 'index,follow';
    }

    /**
     * Display the special page.
     *
     * @param string $subPage
     *
     * @return void
     * @throws MWException
     */
    public function execute($subPage)
    {
        if($_GET['length']==''){
            $this->length='3';
        } else {
            $this->length=$_GET['length'];
        }
        if($_GET['sort']==''){
            $this->sort='oldest';
        } else {
            $this->sort=$_GET['sort'];
        }
        $this->endDate = new DateTime();
        $this->endDate->sub(new \DateInterval('P' . $this->length . 'D'));
        global $wgOut;
        $wgOut->addModules('ext.archirecentchanges');
        $this->languageCode = RequestContext::getMain()->getLanguage()->getCode();

        $output = $this->getOutput();
        $this->setHeaders();

        $output->addHTML('<div class="latest-block" style="display:flex;flex-direction:column;">');

        //Dernières modifications
        $addresses=$this->outputRecentChanges();
        $output->addWikiTextAsInterface('[[Special:Modifications récentes|' . wfMessage('allrecentchangesPage')->parse() . ']]');
        
        $output->addHTML('<button id="voir-plus" class="mw-ui-button" style="position:absolute;bottom:10px;left:45%;" data-sort="'.$this->sort.'" data-length="'.$this->length.'" data-dateEnd="'.$this->endDate->format('c').'">Voir la prochaine periode</button>');
        $output->addHTML('<form action="#" method="get" style="display:flex;align-items:baseline;flex-wrap:wrap">
                            <label for="sort">Trier par </label>
                            <select name="sort" id="sort" style="width:unset;margin:0px 10px;">
                                <option value="biggest" ' . ($this->sort == 'biggest' ? 'selected' : '') . '>tailles décroissante de modification</option>
                                <option value="oldest" ' . ($this->sort == 'oldest' ? 'selected' : '') . '>date décroissante de modification</option>
                                <option value="newOnly" ' . ($this->sort == 'newOnly' ? 'selected' : '') . '>nouvelles pages uniquement</option>
                            </select>
                            <label for="length">sur la période </label>
                            <select name="length" id="length" style="width:unset;margin:0px 10px;">
                                <option value="1" ' . ($this->length == '1' ? 'selected' : '') . '>dernières 24h</option>
                                <option value="3" ' . ($this->length == '3' ? 'selected' : '') . '>derniers 3 jours</option>
                                <option value="7" ' . ($this->length == '7' ? 'selected' : '') . '> dernière semaine</option>
                            </select>
                            
                            <input type="submit" value="Valider">
                        </form>');
        
        

        $output->addHTML('</div>'); // End of Latest block
    }

    /**
     * Return the special page category.
     *
     * @return string
     */
    public function getGroupName()
    {
        return 'pages';
    }
}
