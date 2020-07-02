<?php

namespace ArchiTweaks;

use ConfigException;
use Maintenance;
use MediaWiki\MediaWikiServices;
use SMW\ApplicationFactory;
use SMW\CsvResultPrinter;
use SMW\MediaWiki\Api\ApiRequestParameterFormatter;
use SMW\MediaWiki\Api\Ask;
use SMWQueryProcessor;
use SMWQueryResult;
use SplTempFileObject;
use TextExtracts\ExtractFormatter;
use Title;
use WikiPage;

$IP = getenv('MW_INSTALL_PATH');
if ($IP === false) {
    $IP = __DIR__ . '/../../..';
}
require_once("$IP/maintenance/Maintenance.php");

/**
 * Class ExportCsv
 */
class ExportCsv extends Maintenance
{
    private function getParams($limit, $offset = 0, $headers = 'show')
    {
        // On transforme la requête brute en paramètres d'API.
        $parameterFormatter = new ApiRequestParameterFormatter(
            ['query' =>
                '[[Type::Adresse]]' .
                '|?Adresse complète' .
                '|?Adresse' .
                '|?Numéro de rue' .
                '|?Ville' .
                '|?Pays' .
                '|?Coordonnées' .
                '|?Image principale ' .
                '|?Événement' .
                '|?Personne' .
                '|?Inscription' .
                '|?Langue' .
                '|?Source' .
                '|format=csv' .
                '|mainlabel=Titre' .
                '|limit=' . $limit .
                '|offset=' . $offset .
                '|headers=' . $headers
            ]
        );

        return SMWQueryProcessor::getComponentsFromFunctionParams($parameterFormatter->getAskApiParameters(), false);
    }

    /**
     * @param $limit
     * @param int $offset
     * @param string $format
     * @param string $headers
     * @return SMWQueryResult
     */
    private function getResults($limit, $offset = 0, $format = 'csv', $headers = 'show')
    {
        list($queryString, $parameters, $printouts) = $this->getParams($limit, $offset, $headers);

        // On ajoute la colonne principale.
        SMWQueryProcessor::addThisPrintout($printouts, $parameters);

        return ApplicationFactory::getInstance()->getStore()->getQueryResult(
            SMWQueryProcessor::createQuery($queryString, SMWQueryProcessor::getProcessedParams($parameters),
                SMWQueryProcessor::SPECIAL_PAGE,
                $format,
                $printouts)
        );
    }

    /**
     * @param $limit
     * @param int $offset
     * @param string $headers
     * @return string
     */
    private function getCsv($limit, $offset = 0, $headers = 'show')
    {
        list($queryString, $parameters, $printouts) = $this->getParams($limit, $offset, $headers);

        $printer = new CsvResultPrinter('csv');

        return $printer->getResult(
            $this->getResults($limit, $offset, 'csv', $headers),
            SMWQueryProcessor::getProcessedParams($parameters),
            SMW_OUTPUT_FILE
        );
    }

    /**
     * @return int
     */
    private function getCount()
    {
        // Limite bidon puisque le format "count" l'ignore.
        return $this->getResults(1, 0, 'count')->getCountValue();
    }

    /**
     * @param array $row
     * @return string
     */
    private function csvToString(array $row)
    {
        $newTmp = new SplTempFileObject();
        $newTmp->fputcsv($row);
        $newTmp->rewind();

        $result = '';
        while (!$newTmp->eof()) {
            $result .= $newTmp->fgets();
        }

        return $result;
    }

    /**
     * @param $delimiter
     * @param $str
     * @param string $escapeChar
     * @return false|string[]
     * @link https://r.je/php-explode-split-with-escape-character
     */
    private function explodeEscaped($delimiter, $str, $escapeChar = '\\')
    {

        //Just some random placeholders that won't ever appear in the source $str

        $double = "\0\0\0_doub";

        $escaped = "\0\0\0_esc";

        $str = str_replace($escapeChar . $escapeChar, $double, $str);

        $str = str_replace($escapeChar . $delimiter, $escaped, $str);


        $split = explode($delimiter, $str);

        foreach ($split as &$val) $val = str_replace([$double, $escaped], [$escapeChar, $delimiter], $val);

        return $split;

    }

    /**
     * @param $csv
     * @param $headers
     * @throws ConfigException
     * @see CsvResultPrinter::getResultText()
     */
    private function addSectionsAndOutput($csv, $headers)
    {
        $oldTmp = new SplTempFileObject();
        $oldTmp->fwrite($csv);
        $oldTmp->rewind();

        $j = 0;
        while (!$oldTmp->eof()) {
            $row = $oldTmp->fgetcsv();

            if (isset($row[0])) {

                if ($j == 0 && $headers == 'show') {
                    // On ajoute l'en-tête.
                    $row[] = 'Description';
                } else {
                    $title = Title::newFromText(stripcslashes($row[0]));
                    $page = WikiPage::newFromID($title->getArticleID());
                    $content = $page->getContent();

                    $descriptions = [];

                    // On a besoin des dates des événements.
                    $dates = [];
                    foreach ($this->explodeEscaped(',', $row[8]) as $event) {
                        $eventInfo = explode(';', $event);
                        if (isset($eventInfo[1])) {
                            $dates[] = trim(stripcslashes($eventInfo[1]));
                        }
                    }

                    foreach ($content->getParserOutput($title, null, null, false)->getSections() as $section) {
                        if ($section['toclevel'] == 1) {
                            // Extraction de la date.
                            preg_match('/\|\s?date\s?=\s?([^|}]+)/', $content->getSection($section['index'])->getWikitextForTransclusion(), $matches);

                            if (isset($matches[1])) {
                                $date = trim($matches[1]);

                                // On ne prend que les sections correspondant à un événement.
                                if (in_array($date, $dates)) {
                                    // Extraction du texte brut.
                                    $output = $content->getSection($section['index'])->getParserOutput($title, null, null, false);
                                    $formatter = new ExtractFormatter(
                                        $output->getText(),
                                        true,
                                        MediaWikiServices::getInstance()
                                            ->getConfigFactory()
                                            ->makeConfig('textextracts')
                                    );
                                    $text = trim(
                                        preg_replace(
                                            "/" . ExtractFormatter::SECTION_MARKER_START . '(\d)' . ExtractFormatter::SECTION_MARKER_END . "(.*?)$/m",
                                            '',
                                            $formatter->getText()
                                        )
                                    );

                                    $descriptions[] = addcslashes($date, ',;') . '; ' .
                                        addcslashes($text, ',;');
                                }
                            }
                        }
                    }

                    // On ajoute la colonne.
                    $row[] = implode(',', $descriptions);
                }

                $this->output($this->csvToString($row));
            }

            $j++;
        }
    }

    /**
     * @return void
     * @throws ConfigException
     * @see Ask
     */
    public function execute()
    {
        global $smwgQDefaultLimit;

        // SMW refusera de tout récupérer en une seule requête.
        for ($i = 0; $i < $this->getCount(); $i += $smwgQDefaultLimit) {
            if ($i == 0) {
                $headers = 'show';
            } else {
                $headers = 'hide';
            }

            $this->addSectionsAndOutput($this->getCsv($smwgQDefaultLimit, $i, $headers), $headers);
        }
    }
}

$maintClass = ExportCsv::class;
require_once RUN_MAINTENANCE_IF_MAIN;
