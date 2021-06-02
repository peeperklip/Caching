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

    private function __construct(?\Redis $redis = null, ?string $host = null, ?int $port = null, ?string $password = null)
    {
        if ($redis instanceof \Redis) {
            $this->redis = $redis;
        }

        if ($redis === null && ($host === null || $port === null)) {
            throw new \RuntimeException(sprintf("Can not instantiate %s", self::class));
        }

        if ($redis === null) {
            $redis = new \Redis();

            if (!$redis->connect($host, $port)) {
                throw new \RuntimeException(sprintf("Can not instantiate %s. Could not connect to redis", self::class));
            }

            if ($password !== null && !$redis->auth($password)) {
                throw new \RuntimeException(sprintf("Can not instantiate %s. Authentication to redis failed", self::class));
            }
            $this->redis = $redis;
        }

        $this->deferred = [];

    }

    public static function createFromCredentials(string $host, int $port, string $password = null): self
    {
        return new self(null, $host, $port, $password);
    }

    public static function createFromRedisObject(\Redis $redis): self
    {
        return new self($redis);
    }

    public function getItem($key)
    {
        if (!$this->hasItem($key)) {
            throw new InvalidArgumentException(sprintf('Requested key was never stored into cache: %s', $key));
        }

        return $this->redis->get($key);
    }

    public function getItems(array $keys = array()): array
    {
        $requestedKeys = array_values($keys);
        if (array_diff(array_values($requestedKeys), $this->redis->keys('*')) !== []) {
            throw new InvalidArgumentException(sprintf('(Some) requested key(s) ware never stored into cache: %s', implode(', ', $requestedKeys)));
        }
        $items = [];

        array_walk($keys, function ($key) use (&$items) {
            $items[] =  $this->redis->get($key);
        });

        return $items;
    }

    public function hasItem($key)
    {
        return 1 === $this->redis->exists($key);
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
        // TODO validate the key
        // Throw invalid argument exception if the value sucks
        array_walk($keys, function (string $items) {
            $this->redis->del($items);
        });
    }

    public function save(CacheItemInterface $item)
    {
        return $this->redis->set($item->getKey(), $item, ['nx', 'ex' => self::DEFAULT_TTL]);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferred[$item->getKey()] = $item;
    }

    public function commit()
    {
        $failed = array_filter($this->deferred, function (CacheItemInterface $item) {
            return $this->save($item);
        });

        if ($failed === []) {
            $this->deferred = [];

            return true;
        }

        return false;
    }
}
