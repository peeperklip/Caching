<?php

namespace Peeperklip;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ArrayItemPool implements CacheItemPoolInterface
{
    private array $container;
    private array $deferredContainer;

    public function __construct()
    {
        $this->container = [];
        $this->deferredContainer = [];
    }

    public function getItem($key)
    {
        $this->validateRequestedKey($key);

        if ($this->hasItem($key)) {
            return $this->container[$key];
        }

        return new CacheItem($key);
    }

    public function getItems(array $keys = array()): array
    {
        array_walk($keys, function ($key) {
            $this->validateRequestedKey($key);
        });

        $return = [];

        array_walk($keys, function ($key) use (&$return) {
            $return[] = $this->getItem($key);
        });

        return $return;
    }

    public function hasItem($key): bool
    {
        return array_key_exists($key, $this->container);
    }

    public function clear()
    {
        $this->container = [];
    }

    public function deleteItem($key)
    {
        unset($this->container[$key]);
    }

    public function deleteItems(array $keys)
    {
        array_walk($keys, 'deleteItem');
    }

    public function save(CacheItemInterface $item)
    {
        $this->container[$item->getKey()] = $item;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        $this->deferredContainer[$item->getKey()] = $item;
    }

    public function commit()
    {
        array_walk($this->deferredContainer, function ($value, $key) {
            $this->container[$key] = $value;
        });

        $this->deferredContainer = [];
    }

    private function validateRequestedKey($value): void
    {
        if ($value !== 0 && empty($value)) {
            throw new InvalidArgumentException('Key should not contain empty values');
        }

        if (in_array(gettype($value), ['boolean', 'array', 'object', 'resource'])) {
            throw new InvalidArgumentException('Keys should be scalar');
        }
    }
}
