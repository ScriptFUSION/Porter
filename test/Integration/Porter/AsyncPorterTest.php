<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter;

use Amp\Loop;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;

/**
 * @see Porter
 */
final class AsyncPorterTest extends PorterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->specification = new AsyncImportSpecification($this->resource);
    }

    public function testImportAsync(): void
    {
        Loop::run(function () {
            $records = $this->porter->importAsync($this->specification);
            yield $records->advance();

            self::assertSame(['foo'], $records->getCurrent());
        });
    }

    public function testImportOneAsync(): void
    {
        Loop::run(function () {
            self::assertSame(['foo'], yield $this->porter->importOneAsync($this->specification));
        });
    }
}
