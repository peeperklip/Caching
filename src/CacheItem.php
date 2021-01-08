<?php

namespace Peeperklip;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private $expiresAt;
    private $value;
    private string $key;

    public function __construct($id)
    {
        $this->key = $id;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function get()
    {
        return $this->value;
    }

    public function isHit()
    {
        if (null === $this->value) {
            return false;
        }

        if (time() >= $this->expiresAt) {
            return false;
        }

        return true;
    }

    public function set($value)
    {
        $this->value = $value;
    }

    public function expiresAt($expiration)
    {
        $this->expiresAt = $expiration;
    }

    public function expiresAfter($time)
    {
        if (is_int($time)) {
            $this->expiresAt = time() + $time;
        } elseif($time instanceof \DateInterval) {
            $this->expiresAt = $time->format('%s');
        } elseif (null === $time) {
            $this->expiresAfter(300);
        }
    }
}