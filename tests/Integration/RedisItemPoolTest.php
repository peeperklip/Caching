<?php

namespace Peeperklip\Cache\Tests\Integration;

use Peeperklip\CacheItem;
use Peeperklip\InvalidArgumentException;
use Peeperklip\RedisItemPool;
use PHPUnit\Framework\TestCase;

class RedisItemPoolTest extends TestCase
{
    public function testCreateFromCredentialsWillReturnARedisItemPoolInstanceUsingCredentials(): void
    {
        $sut = RedisItemPool::createFromCredentials('redis', 6379);
        self::assertInstanceOf(RedisItemPool::class, $sut);
    }

    public function testObjectInstantiationWithPlainRedisObject(): void
    {
        $redis = new \Redis();
        $redis->connect('redis');
        $sut = RedisItemPool::createFromRedisObject($redis);

        self::assertInstanceOf(RedisItemPool::class, $sut);
    }

    public function testCallingClearWillEmptyOutCachePool()
    {
        $sut = $this->createSut();
        $sut->save(new CacheItem('my_key'));
        self::assertTrue($sut->hasItem('my_key'));
        $sut->clear();
        self::assertFalse($sut->hasItem('my_key'));
    }

    public function testDeleteWillDeleteSpecifiedCacheItem()
    {
        $sut = $this->createSut();
        $sut->save(new CacheItem('im_not_here'));
        self::assertTrue($sut->hasItem('im_not_here'));
        $sut->deleteItem('im_not_here');
        self::assertFalse($sut->hasItem('im_not_here'));
    }

    public function testDeleteWillNotCauseAnyProblemsWhenTryingToDeleteAnItemThatDoesNotExist()
    {
        $sut = $this->createSut();
        self::assertNull($sut->deleteItem('im_not_here'));
    }

    public function testSaveStoresANewCacheItem(): void
    {
        $sut = $this->createSut();
        self::assertFalse($sut->hasItem('my_cache_item'));
        $sut->save(new CacheItem('my_cache_item'));
        self::assertTrue($sut->hasItem('my_cache_item'));
    }

    public function testSaveDeferredWillNotDirectlyStoreIntoCacheBeforeTheCommitMethodIsCalled(): void
    {
        $sut = $this->createSut();
        self::assertFalse($sut->hasItem('my_cache_item'));
        $sut->saveDeferred(new CacheItem('my_cache_item'));
        self::assertFalse($sut->hasItem('my_cache_item'));
        $sut->commit();
        self::assertTrue($sut->hasItem('my_cache_item'));
    }

    public function testSaveDeferred(): void
    {
        $sut = $this->createSut();

        $CI = new CacheItem('i_will_be_replaced');
        $CI->set('my_value_will_be_replaced');
        $sut->save($CI);

        $cashedItem = $sut->getItem('i_will_be_replaced');

        self::assertSame('my_value_will_be_replaced', $cashedItem->get());

        $DCI = new CacheItem('i_will_be_replaced');
        $DCI->set('my_value_is_replaced_by_now');
        $sut->saveDeferred($DCI);

        self::assertSame('my_value_will_be_replaced', $sut->getItem('i_will_be_replaced')->get());

        self::assertTrue($sut->commit());

        self::assertSame('my_value_is_replaced_by_now', $sut->getItem('i_will_be_replaced')->get());
    }

    public function testGetItemsWillTrowExceptionIfTheKeyIsNotSet(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('(Some) requested key(s) ware never stored into cache: im_not_here, neither_am_i');
        $this->createSut()->getItems(['im_not_here', 'neither_am_i']);
    }

    private function createSut() : RedisItemPool
    {
        $redisItemPool = RedisItemPool::createFromCredentials('redis', 6379);
        $redisItemPool->clear();

        return $redisItemPool;
    }
}
