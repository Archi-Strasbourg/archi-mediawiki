<?php

namespace ArchiTweaks;

use ApiMain;
use ApiQueryRecentChanges;
use DerivativeRequest;

class ApiQueryArchiRecentChanges extends ApiQueryRecentChanges
{
    /**
     * @param array $options
     * @return array|mixed|object|null
     */
    private function apiRequest(array $options)
    {
        $params = new DerivativeRequest(
            $this->getRequest(),
            $options
        );
        $api = new ApiMain($params);
        $api->execute();

        return $api->getResult()->getResultData();
    }

    /**
     * @param array $a
     * @param array $b
     * @return int
     */
    private function sortByStreet(array $a, array $b): int
    {
        $sort = strnatcasecmp($a['street'], $b['street']);

        if ($sort == 0) {
            return strnatcasecmp($a['number'], $b['number']);
        }

        return $sort;
    }

    /**
     * @return void
     */
    public function execute()
    {
        parent::execute();

        $result = $this->getResult();
        $byStreet = [];

        foreach ($result->getResultData(['query', $this->getModuleName()]) as $item) {
            if (isset($item['title'])) {
                $street = $this->apiRequest(
                    [
                        'action' => 'ask',
                        'query' => '[[' . $item['title'] . ']]|?Rue|?Numéro',
                    ]
                );

                if (isset($street['query']['results'][$item['title']])) {
                    $item['street'] = $street['query']['results'][$item['title']]['printouts']['Rue'][0]['fulltext'];
                    $item['number'] = $street['query']['results'][$item['title']]['printouts']['Numéro'][0];
                    $byStreet[] = $item;
                }
            }
        }

        usort($byStreet, [$this, 'sortByStreet']);

        foreach ($byStreet as $item) {
            $result->addValue(['sortedQuery', $this->getModuleName()], null, $item);
        }
    }
}
