<?php

use Peeperklip\CacheItem;
use Peeperklip\CacheItemPool;
use PHPUnit\Framework\TestCase;

class CacheItemPoolTest extends TestCase
{
    private const NON_EXISTENT_KEY = 'thiskeydoesnotexist';
    private const THIS_KEY_EXISTS = 'iexist';

    public function testGetItemsWillThrowAnExceptionIfTheRequestedKeyIsNotFound(): void
    {
        $sut = new CacheItemPool();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This key was not found');

        $sut->getItem(self::NON_EXISTENT_KEY);
    }

    public function testGetItemsWillThrowAnExceptionIfTheRequestedKeyIsAnEmptyValue(): void
    {
        $sut = new CacheItemPool();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Key should not contain empty values');

        $sut->getItem(null);
    }

    public function testGetItemsWillThrowAnExceptionIfTheRequestedKeyIsNotAScalar(): void
    {
        $sut = new CacheItemPool();
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Keys should be scalar');

        $sut->getItem(true);
    }

    public function testHasItemWillReturnSomething(): void
    {
        $sut = new CacheItemPool();

        $ci = new CacheItem(self::THIS_KEY_EXISTS);
        $sut->save($ci);

        self::assertSame(spl_object_id($ci), spl_object_id($sut->getItem(self::THIS_KEY_EXISTS)));
    }

    public function testGetItemsReturnsEverythingThatIsRequested(): void
    {
        $sut = new CacheItemPool();

        $ci0 = new CacheItem(self::THIS_KEY_EXISTS . '_0');
        $ci1 = new CacheItem(self::THIS_KEY_EXISTS . '_1');
        $ci2 = new CacheItem(self::THIS_KEY_EXISTS . '_2');
        $ci3 = new CacheItem(self::THIS_KEY_EXISTS . '_3');

        $sut->save($ci0);
        $sut->save($ci1);
        $sut->save($ci2);
        $sut->save($ci3);

        $resultSet = $sut->getItems([
                self::THIS_KEY_EXISTS . '_0',
                self::THIS_KEY_EXISTS . '_1',
                self::THIS_KEY_EXISTS . '_2',
                self::THIS_KEY_EXISTS . '_3',
            ]);

        self::assertCount(4, $resultSet);
        self::assertContainsOnlyInstancesOf(CacheItem::class, $resultSet);
    }

    public function testGetItemFirstReturnsTheRequestedObjectThenThrowsExceptionAfterItsRemovedFromCache(): void
    {
        $sut = new CacheItemPool();

        $ci = new CacheItem(self::THIS_KEY_EXISTS);

        $sut->save($ci);

        $ci = $sut->getItem(self::THIS_KEY_EXISTS);

        $sut->deleteItem(self::THIS_KEY_EXISTS);

        $sut->getItem(self::THIS_KEY_EXISTS);
    }

    //saveDeferred
    public function testSaveDeffered()
    {
        $sut = new CacheItemPool();

        $ci = new CacheItem('my_deferred');
        $ci->set(1234);
        $ci->expiresAfter(1000);

        $sut->saveDeferred($ci);

        self::assertNull($ci->get());
        self::assertFalse($ci->isHit());
    }
}