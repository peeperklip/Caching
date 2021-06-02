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
        $this->validateKey($key);

        if (!$this->hasItem($key)) {
            return new CacheItem($key);
        }

        //Not sure if it is a problem if you do not get the same instance you stored. The values in the object are the same

        $storedData = json_decode($this->redis->get($key), true, 512, JSON_THROW_ON_ERROR);

        $ci = new CacheItem($key);
        $ci->set($storedData['value']);
        $ci->expiresAfter($storedData['expiresAt']);

        return $ci;
    }

    public function getItems(array $keys = array()): array
    {
        $requestedKeys = array_values($keys);
        if (array_diff(array_values($requestedKeys), $this->redis->keys('*')) !== []) {
            throw new InvalidArgumentException(sprintf('(Some) requested key(s) ware never stored into cache: %s', implode(', ', $requestedKeys)));
        }
        $items = [];

        array_walk($keys, function ($key) use (&$items) {
            $items[] =  $this->getItem($key);
        });

        return $items;
    }

    public function hasItem($key)
    {
        $this->validateKey($key);
        return 1 === $this->redis->exists($key);
    }

    public function clear()
    {
        $allKeys = $this->redis->keys('*');
        $this->deleteItems($allKeys);
    }

    public function deleteItem($key)
    {
        $this->validateKey($key);
        if (!$this->hasItem($key)) {
            return;
        }

        $this->redis->del($key);
    }

    public function deleteItems(array $keys)
    {
        array_walk($keys, function (string $key) {
            $this->deleteItem($key);
        });
    }

    public function save(CacheItemInterface $item)
    {
        $key = $item->getKey();
        $this->validateKey($key);

        if ($this->hasItem($key)) {
            $this->deleteItem($key);
        }
        return $this->redis->set($key, json_encode($item), ['nx', 'ex' => self::DEFAULT_TTL]);
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->validateKey($item->getKey());
        $this->deferred[$item->getKey()] = $item;
    }

    public function commit()
    {
        $failed = array_filter($this->deferred, function (CacheItemInterface $item) {
            return !$this->save($item);
        });

        if ($failed === []) {
            $this->deferred = [];

            return true;
        }

        return false;
    }

    private function validateKey($key)
    {
        if (!is_scalar($key) || is_bool($key)) {
            throw new InvalidArgumentException(sprintf('Invalid key. Scalar value was expected, instead got %s', gettype($key)));
        }
    }
}
