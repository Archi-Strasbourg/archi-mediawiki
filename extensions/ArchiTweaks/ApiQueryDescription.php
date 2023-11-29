<?php

namespace ArchiTweaks;

use ApiQueryBase;
use TextExtracts\TextTruncator;
use Title;
use TextExtracts\ExtractFormatter;

/**
 * Class ApiQueryDescription
 *
 * @package ArchiTweaks
 */
class ApiQueryDescription extends ApiQueryBase
{

    /**
     * @param $options
     *
     * @return mixed
     */
    private function apiRequest($options)
    {
        $params = new \DerivativeRequest(
            $this->getRequest(),
            $options
        );
        $api = new \ApiMain($params);
        $api->execute();

        return $api->getResult()->getResultData();
    }

    /**
     * @param $text
     *
     * @return string
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
     *
     * @return mixed|string
     */
    private function getIntro(Title $title)
    {
        global $wgMemc;

        $id = $title->getArticleID();

        $key = wfMemcKey('archidescription', $id, $title->getTouched());
        $result = $wgMemc->get($key);

        if (!$result) {
            // On refait manuellement ce que fait TextExtracts pour pouvoir le faire sur la section 1.
            $extracts = $this->apiRequest(
                [
                    'action' => 'parse',
                    'pageid' => $id,
                    'prop' => 'text',
                    'section' => 1,
                ]
            );

            $result = '';
            if (isset($extracts['parse']['text'])) {
                $result = $this->convertText($extracts['parse']['text']);
            }

            $wgMemc->set($key, $result);
        }

        return $result;
    }

    public function execute()
    {
        $result = $this->getResult();

        foreach ($this->getPageSet()->getGoodTitles() as $id => $title) {
            $description = '';

            $properties = $this->apiRequest(
                [
                    'action' => 'ask',
                    'query' => '[[' . $title . ']]|?Adresse complète',
                ]
            );

            $address = '';
            if (isset($properties['query']['results'][(string)$title]) && !empty($properties['query']['results'][(string)$title]['printouts']['Adresse complète'])) {
                $address = $properties['query']['results'][(string)$title]['printouts']['Adresse complète'][0]['fulltext'];
            }

            $intro = $this->getIntro($title);

            $description .= $address;
            if (!empty($address) && !empty($intro)) {
                $description .= ' - ';
            }
            $description .= $intro;

            $result->addValue(
                [
                    'query',
                    'pages',
                    $id,
                ],
                'description',
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
     * @return string
     */
    public function getDescription(): string
    {
        return "Remplace la propriété wikidataDescription normalement retournée par l'extension Wikidata, pour que Special:Nearby puisse l'utiliser.";
    }

    /**
     * @return bool
     */
    public function isInternal(): bool
    {
        return TRUE;
    }

    /**
     * @return array
     */
    protected function getExamples(): array
    {
        return [
            'action=query&prop=archiDescription&titles=Adresse:14 Avenue de la Marseillaise (Strasbourg)',
        ];
    }

}