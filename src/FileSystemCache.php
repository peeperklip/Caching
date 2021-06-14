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
             throw new \Peeperklip\InvalidArgumentException('not a valid dir');
        }

        $this->varDir = $varDir;
    }

    public function getItem($key)
    {
        if ($this->hasItem($this->getFileName($key))) {
            //exitsts
        }

        // does not exist
    }

    public function getItems(array $keys = array())
    {

        // TODO: Implement getItems() method.
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

        if (is_dir($key)) {
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
        if (base64_encode(base64_decode($item->get(), true)) !== $item->get()) {
            return false;
        }

        // write file

        return true;
    }

    public function saveDeferred(CacheItemInterface $item)
    {
        // TODO: Implement saveDeferred() method.
    }

    public function commit()
    {
        // TODO: Implement commit() method.
    }

    private function getFileName($path)
    {
        return $this->varDir . DIRECTORY_SEPARATOR . $path;
    }
}
