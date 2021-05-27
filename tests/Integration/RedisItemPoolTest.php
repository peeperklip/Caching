<?php

namespace Peeperklip\Cache\Tests\Integration;

use Peeperklip\RedisItemPool;
use PHPUnit\Framework\TestCase;

class RedisItemPoolTest extends TestCase
{
    public function testJustAnEmptyTest(): void
    {
        self::markTestSkipped();
    }

    public function testCreateFromCredentialsWillReturnARedisItemPoolInstanceUsingCredentials(): void
    {
        $sut = RedisItemPool::createFromCredentials('redis', 6379);
        self::assertInstanceOf(RedisItemPool::class, $sut);
    }

    public function testObjectInstantiationOne(): void
    {
        self::markTestSkipped();
    }

    public function testSaveStoresANewCacheItem(): void
    {
        self::markTestSkipped();
    }

    public function testSaveDeferredWillNotDirectlyStoreIntoCacheBeforeTheCommitMethodIsCalled(): void
    {
        self::markTestSkipped();
    }

    public function testKeysStoredImmediatelyWillNotConflictWithDeferredItems(): void
    {
        self::markTestSkipped();
    }

    public function testKeysStoredDeferredWillNotConflictWithItemsStoredImmediately(): void
    {
        self::markTestSkipped();
    }

    public function testGetItemWillTrowExceptionIfTheKeyIsNotSet(): void
    {
        self::markTestSkipped();
    }

    public function testGetItemWillReturnACacheItemIfTheKeyExists(): void
    {
        self::markTestSkipped();
    }

    public function testCommitWillSaveTheDeferredItems(): void
    {
        self::markTestSkipped();
    }
}
