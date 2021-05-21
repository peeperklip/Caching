<?php

declare(strict_types=1);

namespace Peeperklip;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class RedisItemPool implements CacheItemPoolInterface
{
    private \Redis $redis;

    private const DEFAULT_TTL = 60;

    private array $deferred;

    public function __construct(string $host, int $port, $password)
    {
        $this->deferred = [];
        $redis = new \Redis();
        $redis->connect($host, $port);
        $redis->auth($password);
        $this->redis = $redis;
    }

    public function getItem($key)
    {
        return $this->redis->get($key);
    }

    public function getItems(array $keys = array()): array
    {
        $items = [];

        array_walk($keys, function ($key) use (&$items) {
            $items[] =  $this->redis->get($key);
        });

        return $items;
    }

    public function hasItem($key)
    {
        return $this->redis->exists($key);
    }

    public function clear()
    {
        $allKeys = $this->redis->keys('*');
        $this->deleteItems($allKeys);
    }

    public function deleteItem($key)
    {
        if (!$this->hasItem($key)) {
            return;
        }

        $this->redis->del($key);
    }

    public function deleteItems(array $keys)
    {
        array_walk($keys, function (string $items) {
            $this->redis->del($items);
        });
    }

    public function save(CacheItemInterface $item)
    {
        $this->redis->set($item->getKey(), $item->get(), ['nx', 'ex' => self::DEFAULT_TTL]);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;
    }

    public function commit()
    {
        array_walk($this->deferred, function (CacheItemInterface $item) {
            $this->save($item);
        });

        return $this->deferred = [];
    }
}
