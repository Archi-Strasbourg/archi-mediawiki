<?php

namespace ArchiTweaks;

use Maintenance;
use SMW\ApplicationFactory;
use SMW\CsvResultPrinter;
use SMW\MediaWiki\Api\ApiRequestParameterFormatter;
use SMW\MediaWiki\Api\Ask;
use SMWQueryProcessor;
use SMWQueryResult;

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
     * @return void
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

            $this->output($this->getCsv($smwgQDefaultLimit, $i, $headers));
        }
    }
}

$maintClass = ExportCsv::class;
require_once RUN_MAINTENANCE_IF_MAIN;
