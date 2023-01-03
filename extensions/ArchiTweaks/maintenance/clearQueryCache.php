<?php

namespace ArchiTweaks;

use Maintenance;
use ObjectCache;
use RedisConnectionPool;
use RedisException;

$IP = getenv('MW_INSTALL_PATH');
if ($IP === false) {
    $IP = __DIR__ . '/../../..';
}
require_once("$IP/maintenance/Maintenance.php");

class ClearQueryCache extends Maintenance
{

    /**
     * @return void
     * @throws RedisException
     * @see SpecialRunQueryCache::printPage()
     */
    public function execute()
    {
        global $wgObjectCaches, $wgDBname;

        $cache = ObjectCache::getInstance('redis');
        $pool = RedisConnectionPool::singleton([]);
        $connection = $pool->getConnection(current($wgObjectCaches['redis']['servers']));

        // On supprime tout ce qui correspond au préfixe.
        $keys = $connection->keys(
            implode(
                ':',
                [
                    $wgDBname,
                    'archi-tweaks',
                    'query',
                    '*'
                ]
            )
        );
        $result = $cache->deleteMulti($keys);

        if ($result) {
            $this->output(count($keys) . ' éléments supprimés' . PHP_EOL);
        } else {
            $this->error('Erreur lors de la vidange du cache');
        }
    }
}

$maintClass = ClearQueryCache::class;
require_once RUN_MAINTENANCE_IF_MAIN;
