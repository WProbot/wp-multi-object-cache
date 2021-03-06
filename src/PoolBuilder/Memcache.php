<?php

namespace WPMultiObjectCache\PoolBuilder;

use Cache\Adapter\Memcache\MemcacheCachePool;
use Psr\Cache\CacheItemPoolInterface;

class Memcache implements PoolBuilder
{

    /**
     * Creates a pool
     *
     * @param array $config Config to use to create the pool.
     *
     * @return CacheItemPoolInterface
     * @throws \RuntimeException
     */
    public function create(array $config = [])
    {
        $config = \wp_parse_args(
            $config,
            [
            'server' => ['127.0.0.1', 11211],
            ]
        );

        $memcache = $this->createInstance();
        $this->initialize($memcache, $config);

        return new MemcacheCachePool($memcache);
    }

    /**
     * Initializes the Memcache connection.
     *
     * @param \Memcache $memcache Memcache instance.
     * @param array     $config   Configuration.
     *
     * @return bool
     * @throws \RuntimeException
     */
    private function initialize($memcache, $config)
    {
        if ($config['persistent']) {
            return call_user_func_array([$memcache, 'pconnect'], $config['server']);
        }

        $servers = [];
        if (! empty($config['server'])) {
            $servers = [$config['server']];
        }

        if (! empty($config['servers'])) {
            $servers = $config['servers'];
        }

        $results = array_map(
            function ($server) use ($memcache) {
                call_user_func_array([$memcache, 'addServer'], $server);
            },
            $servers
        );

        $success = array_filter($results);

        if (count($success) !== count($results)) {
            throw new \RuntimeException('Memcache failed to add servers to it\'s pool.');
        }

        return true;
    }

    /**
     * Creates a new Memcache instance.
     *
     * @return \Memcache Memcache instance.
     * @throws \RuntimeException
     */
    protected function createInstance()
    {
        if (! class_exists('Memcache')) {
            throw new \RuntimeException(
                'The class Memcache could not be found, please install the PHP Memcache extension'
            );
        }

        return new \Memcache();
    }
}
