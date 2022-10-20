<?php

namespace ArchiTweaks;

use MWException;
use ObjectCache;
use PFRunQuery;
use SpecialPage;
use const PHP_QUERY_RFC3986;

class SpecialRunQueryCache extends PFRunQuery
{

    /**
     * @return void
     */
    function __construct()
    {
        parent::__construct();

        /*
         * Cet argument est en dur dans PFRunQuery::__construct()
         * donc on est obligés d'appeler le grand-parent.
         */
        SpecialPage::__construct('RunQueryCache');
    }

    /**
     * @param array $queryArgs
     * @return string
     * @link https://api.drupal.org/api/drupal/vendor%21symfony%21http-foundation%21Request.php/function/Request%3A%3AnormalizeQueryString/9.3.x
     */
    private function normalizeQueryString(array $queryArgs): string
    {
        ksort($queryArgs);
        return http_build_query($queryArgs, '', '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param string $form_name
     * @param bool $embedded
     * @return void
     */
    function printPage($form_name, $embedded = false): void
    {
        global $wgDBname;
        $output = $this->getOutput();

        $cache = ObjectCache::getInstance('redis');
        $cacheKey = implode(
            ':',
            [
                // On met en cache par page et par langue.
                $wgDBname,
                'archi-tweaks',
                'query',
                $this->getLanguage()->getCode(),
                md5($this->normalizeQueryString($this->getRequest()->getValues()))
            ]
        );

        /** @var CachedQuery $cachedQuery */
        if ($cachedQuery = $cache->get($cacheKey)) {
            $cachedQuery->populateOutput($output);
        } else {
            parent::printPage($form_name, $embedded);

            $cache->set(
                $cacheKey,
                new CachedQuery(
                    $output->getPageTitle(),
                    $output->getHTML(),
                    $output->getHeadItemsArray(),
                    $output->getJsConfigVars(),
                    $output->getModules()
                ),
                $cache::TTL_DAY
            );
        }

        // Script pour désactiver l'avertissement à la fermeture sur cette page.
        $this->getOutput()->addModules(['ext.architweaks.disableeditwarning']);
    }

}
