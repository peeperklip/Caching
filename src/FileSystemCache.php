<?php

declare(strict_types=1);

namespace Peeperklip;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

final class FileSystemCache implements CacheItemPoolInterface
{
    private string $varDir;

    public function __construct(string $varDir)
    {
        if (!is_dir($varDir)) {
             throw new InvalidArgumentException('Not a valid dir');
        }

        $this->varDir = $varDir;
    }

    public function getItem($key)
    {
        $result = null;

        if ($this->hasItem($this->getFileName($key))) {
            $result = file_get_contents($this->getFileName($key));
        }

        return new CacheItem($result);
    }

    public function getItems(array $keys = array())
    {
        $result = [];
        array_walk($keys, function (string $key) use (&$result){
            $result[] = $this->getItem($key);
        });

        return $result;
    }

    public function hasItem($key)
    {
        return (!file_exists($this->getFileName($key)));
    }

    public function clear()
    {
        unlink($this->varDir);
    }

    public function deleteItem($key)
    {
        if (in_array($key, ['.', '..'])) {
            return false;
        }

        if (is_dir($this->getFileName($key))) {
            return false;
        }

        if (!file_exists($key)) {
            return false;
        }

        return unlink($key);
    }

    public function deleteItems(array $keys)
    {
        array_walk($keys, function (string $item) {
            $this->deleteItem($item);
        });
    }

    public function save(CacheItemInterface $item)
    {
        file_put_contents($this->getFileName($item->getKey()), $item->get());

        return true;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        throw new CacheException(__METHOD__ . "Not implemented yet");
    }

    public function commit()
    {
        throw new CacheException(__METHOD__ . "Not implemented yet");
    }

    private function getFileName($path)
    {
        return $this->varDir . DIRECTORY_SEPARATOR . $path;
    }
}
