<?php

declare(strict_types=1);

namespace Peeperklip\Cache\Tests\Integration;

use Peeperklip\FileSystemCache;
use PHPUnit\Framework\TestCase;

final class FileSystemTest extends TestCase
{
    private const VAR_DIR = __DIR__ . '/var';
    private FileSystemCache $sut;

    public function setUp(): void
    {
        $this->sut = new FileSystemCache(self::VAR_DIR);
    }

    public function testSut()
    {
        $result = $this->sut->getItem('my_stored_stuff');
        self::assertNull($result->get());
        self::assertSame('my_stored_stuff', $result->getKey());
        self::assertFalse($result->isHit());
        $result->set('my_value');

        $this->sut->save($result);

        $result = $this->sut->getItem('my_stored_stuff');
        $result->expiresAfter(55);

        self::assertSame('my_value', $result->get());
        self::assertSame('my_stored_stuff', $result->getKey());
        self::assertTrue($result->isHit());
    }

    protected function tearDown(): void
    {
        $this->sut->clear();
    }
}
