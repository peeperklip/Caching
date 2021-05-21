<?php

use Peeperklip\CacheItem;
use Peeperklip\ArrayItemPool;
use Peeperklip\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CacheItemPoolTest extends TestCase
{
    private const NON_EXISTENT_KEY = 'thiskeydoesnotexist';
    private const THIS_KEY_EXISTS = 'iexist';
    private const KEY_FOR_DEFERRED_ITEM = 'my_deferred';
    private const DEFAULT_TTL = 10;

    public function testGetItemsWillThrowAnExceptionIfTheRequestedKeyIsAnEmptyValue(): void
    {
        $sut = new ArrayItemPool();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Key should not contain empty values');

        $sut->getItem(null);
    }

    public function testGetItemsWillThrowAnExceptionIfTheRequestedItemIsNotStoredInCache(): void
    {
        $sut = new ArrayItemPool();
        $ci = $sut->getItem(self::NON_EXISTENT_KEY);

        self::assertNull($ci->get());
        self::assertFalse($ci->isHit());
    }

    public function testGetItemsWillThrowAnExceptionIfTheRequestedKeyIsNotAScalar(): void
    {
        $sut = new ArrayItemPool();
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Keys should be scalar');

        $sut->getItem(true);
    }

    public function testHasItemWillReturnSomething(): void
    {
        $sut = new ArrayItemPool();

        $ci = new CacheItem(self::THIS_KEY_EXISTS);
        $sut->save($ci);

        self::assertSame(spl_object_id($ci), spl_object_id($sut->getItem(self::THIS_KEY_EXISTS)));
    }

    public function testGetItemsReturnsEverythingThatIsRequested(): void
    {
        $sut = new ArrayItemPool();

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

    public function testGetItemFirstReturnsTheRequestedObjectFromCacheThenReturnsAMissedItemAfterItsRemovedFromCache(): void
    {
        $sut = new ArrayItemPool();

        $ci = new CacheItem(self::THIS_KEY_EXISTS);
        $ci->set('im being cached');
        $ci->expiresAfter(self::DEFAULT_TTL);
        $sut->save($ci);

        self::assertTrue($ci->isHit());
        self::assertSame(self::THIS_KEY_EXISTS, $ci->getKey());
        self::assertSame('im being cached', $ci->get());

        $sut->getItem(self::THIS_KEY_EXISTS);
        $sut->deleteItem(self::THIS_KEY_EXISTS);

        $ci = $sut->getItem(self::THIS_KEY_EXISTS);

        self::assertFalse($ci->isHit());
        self::assertNull($ci->get());
        self::assertSame(self::THIS_KEY_EXISTS, $ci->getKey());
    }

    public function testSaveDefferedReturnsWillNotReadFromCacheIfItsNotCommittedYetButIWillReadFromCacheAfterCommit()
    {
        $sut = new ArrayItemPool();

        $ci = new CacheItem(self::KEY_FOR_DEFERRED_ITEM);
        $ci->set(1234);
        $ci->expiresAfter(1000);

        $sut->saveDeferred($ci);

        $ci = $sut->getItem(self::KEY_FOR_DEFERRED_ITEM);

        self::assertNull($ci->get());
        self::assertFalse($ci->isHit());

        $sut->commit();

        $ci = $sut->getItem(self::KEY_FOR_DEFERRED_ITEM);

        self::assertSame(1234, $ci->get());
        self::assertTrue($ci->isHit());
    }
    /** @TODO testing the TTL in more detail */
}
