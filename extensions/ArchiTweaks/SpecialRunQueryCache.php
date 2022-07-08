<?php

namespace ArchiTweaks;

use MWException;
use ObjectCache;
use PFRunQuery;
use PFUtils;
use SpecialPage;

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
     * @param string $form_name
     * @param bool $embedded
     * @return void
     * @throws MWException
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
                md5($this->getRequest()->getRequestURL())
            ]
        );

        /** @var CachedQuery $cachedQuery */
        if ($cachedQuery = $cache->get($cacheKey)) {
            $output->addHTML($cachedQuery->html);
            $output->setPageTitle($cachedQuery->title);
            $output->addHeadItems($cachedQuery->head);
        } else {
            parent::printPage($form_name, $embedded);

            $cache->set(
                $cacheKey,
                new CachedQuery($output->getPageTitle(), $output->getHTML(), $output->getHeadItemsArray()),
                $cache::TTL_DAY
            );
        }
    }

}
