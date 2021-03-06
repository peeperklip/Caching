<?php

namespace Peeperklip;

use Psr\Cache\CacheItemInterface;

class CacheItem implements CacheItemInterface, \JsonSerializable
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

        $timeDifferenceInSeconds = ($expiration->getTimestamp() - (new \DateTimeImmutable())->getTimestamp());

        if ($timeDifferenceInSeconds < 1) {
            throw new InvalidArgumentException(sprintf('%s needs to be an instance of %s', __METHOD__, \DateTimeImmutable::class));
        }

        $this->expiresAfter($timeDifferenceInSeconds);
    }

    public function expiresAfter($time)
    {
        if (is_int($time)) {
            $this->expiresAt = time() + $time;
            return;
        }

        if ($time instanceof \DateInterval) {
            $this->expiresAt = $this->getExpirationTimeStampByDateTime($time);
            return;
        }

        if (null === $time) {
            $this->expiresAfter(300);
            return;
        }

        throw new InvalidArgumentException(sprintf("Invalid parameter `time` must be numeric, null or an instance of \DateInterval"));
    }

    private function getExpirationTimeStampByDateTime(\DateInterval $dateInterval):int
    {
        return $dateInterval->format('%s');
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
