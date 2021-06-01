<?php

namespace Peeperklip;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface
{
    private int $expiresAt;
    private $value;
    private string $key;
    private bool $isset;

    public function __construct($id)
    {
        $this->key = $id;
        $this->isset = false;
        $this->expiresAt = 0;
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
        if (!$this->isset) {
            return false;
        }

        if (time() >= $this->expiresAt) {
            return false;
        }

        return true;
    }

    public function set($value)
    {
        $this->isset = true;
        $this->value = $value;
    }

    public function expiresAt($expiration)
    {
        if (!$expiration instanceof \DateTimeInterface) {
            throw new InvalidArgumentException(sprintf('%s needs to be an instance of %s', __METHOD__, \DateTimeImmutable::class));
        }

        $fff = ($expiration->getTimestamp() - (new \DateTimeImmutable())->getTimestamp());

        if ($fff < 1) {
            throw new InvalidArgumentException(sprintf('%s needs to be an instance of %s', __METHOD__, \DateTimeImmutable::class));
        }

        $this->expiresAfter($fff);
    }

    public function expiresAfter($time)
    {
        if (is_int($time)) {
            $this->expiresAt = time() + $time;
        } elseif($time instanceof \DateInterval) {
            $this->expiresAt = $this->getExpirationTimeStampByDateTime($time);
        } elseif (null === $time) {
            $this->expiresAfter(300);
        }
    }

    private function getExpirationTimeStampByDateTime(\DateInterval $dateInterval):int
    {
        return $dateInterval->format('%s');
    }
}
