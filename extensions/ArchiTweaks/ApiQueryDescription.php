<?php

namespace ArchiTweaks;

use ApiQueryBase;
use ApiResult;

/**
 * Class ApiQueryDescription
 *
 * @package ArchiTweaks
 */
class ApiQueryDescription extends ApiQueryBase {

  /**
   * @param $options
   *
   * @return mixed
   */
  private function apiRequest($options) {
    $params = new \DerivativeRequest(
      $this->getRequest(),
      $options
    );
    $api = new \ApiMain($params);
    $api->execute();

    return $api->getResult()->getResultData();
  }

  function execute() {
    $result = $this->getResult();

    foreach ($this->getPageSet()->getGoodTitles() as $id => $title) {
      $description = '';

      $properties = $this->apiRequest(
        [
          'action' => 'ask',
          'query' => '[[' . $title . ']]|?Adresse complète',
        ]
      );

      if (isset($properties['query']['results'][(string) $title]) && !empty($properties['query']['results'][(string) $title]['printouts']['Adresse complète'])) {
        $description .= $properties['query']['results'][(string) $title]['printouts']['Adresse complète'][0]['fulltext'];
      }

      $result->addValue(
        [
          'query',
          'pages',
          $id,
        ],
        'wikidataDescription',
        $description
      );

      $result->addValue(
        [
          'query',
          'pages',
          $id,
          'pageprops',
        ],
        'displaytitle',
        preg_replace('/\(.*\)/', '', $title->getBaseText())
      );
    }
  }

  /**
   * @return array|false|\Message|string
   */
  function getDescription() {
    return "Remplace la propriété wikidataDescription normalement retournée par l'extension Wikidata, pour que Special:Nearby puisse l'utiliser.";
  }

  /**
   * @return bool
   */
  function isInternal() {
    return TRUE;
  }

  /**
   * @return array|bool|string
   */
  protected function getExamples() {
    return [
      'action=query&prop=archiDescription&titles=Adresse:14 Avenue de la Marseillaise (Strasbourg)',
    ];
  }

}