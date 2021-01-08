<?php

namespace Peeperklip;

use Psr\Cache\CacheItemPoolInterface;

class Cacher
{
    /**
     * @var CacheItemPoolInterface
     */
    private CacheItemPoolInterface $cacheItemPool;

    public function __construct(CacheItemPoolInterface $cacheItemPool)
    {
        $this->cacheItemPool = $cacheItemPool;
    }

    public function get($key, callable $callable, int $ttl)
    {
        if ($this->cacheItemPool->hasItem($key)) {
            if ($this->cacheItemPool->getItem($key)->isHit()) {
                return $this->cacheItemPool->getItem($key);
            }
        }

        $cacheItem = new CacheItem($key);
        $returnValue = $callable();
        if (!is_string($returnValue)) {
            throw new \InvalidArgumentException("callable did not respond with a string");
        }

        $cacheItem->expiresAfter($ttl);
        $cacheItem->set($returnValue);

        $this->cacheItemPool->save($cacheItem);

        return $cacheItem;
    }

}