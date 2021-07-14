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
            if (!mkdir($varDir)) {
                throw new \RuntimeException(sprintf('Directory "%s" was not created', $varDir));
            }
        }

        $this->varDir = $varDir;
    }

    public function getItem($key)
    {
        if (!$this->hasItem($key)) {
            return new CacheItem($key);
        }
        $storedData = json_decode(file_get_contents($this->getFileName($key)), true, 512, JSON_THROW_ON_ERROR);

        $ci = new CacheItem($key);
        $ci->set($storedData['value']);
        $ci->expiresAfter($storedData['expiresAt']);

        return $ci;
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
        return file_exists($this->getFileName($key));
    }

    public function clear()
    {
        foreach (scandir($this->varDir) as $item) {
            $varDir = $this->varDir . DIRECTORY_SEPARATOR;
            if (in_array($item, ['.', '..'])) {
                return;
            }

            unlink($varDir . $item);
        }
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
        file_put_contents($this->getFileName($item->getKey()), json_encode($item));

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

    private function getFileName(string $path)
    {
        return $this->varDir . DIRECTORY_SEPARATOR . $path;
    }
}
