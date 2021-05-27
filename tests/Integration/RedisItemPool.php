<?php

namespace Peeperklip\Cache\Tests\Integration;

use PHPUnit\Framework\TestCase;

class RedisItemPool extends TestCase
{
    public function testJustAnEmptyTest()
    {
        self::fail('Nothing yet');
    }

    public function testObjectInstantiationTwo()
    {
    }

    public function testObjectInstantiationOne()
    {
    }


    public function testSaveStoresANewCacheItem()
    {
    }

    public function saveDeferredWillNotDirectlyStoreIntoCasheBeforeTheCommitMethodIsCalled()
    {
    }

    public function testKeysStoredImediatlyWillNotConflicWithDefferredItems()
    {

    }
    public function testKeysStoredDeferredWillNotConflicWithItemsStoredImediatly()
    {

    }

    public function testGetItemWillTrowExceptionIfTheKeyIsNotSet()
    {

    }

    public function testGetItemWillReturnACacheItemIfTheKeyExists()
    {

    }

    public function testCommitWillSaveTheDeferredItems()
    {

    }
}
