<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter;

use Amp\Loop;
use Amp\Producer;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Transform\FilterTransformer;

/**
 * @see Porter
 */
final class PorterAsyncTest extends PorterTest
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

            self::assertTrue(yield $records->advance());
            self::assertSame(['foo'], $records->getCurrent());
        });
    }

    public function testImportOneAsync(): void
    {
        Loop::run(function () {
            self::assertSame(['foo'], yield $this->porter->importOneAsync($this->specification));
        });
    }

    public function testFilterAsync(): void
    {
        $this->resource->shouldReceive('fetchAsync')->andReturn(
            new Producer(static function (\Closure $emit): \Generator {
                foreach (range(1, 10) as $integer) {
                    yield $emit([$integer]);
                }
            })
        );

        $this->specification->addTransformer(
            new FilterTransformer(static function (array $record): int {
                return $record[0] % 2;
            })
        );

        Loop::run(function () {
            $records = $this->porter->importAsync($this->specification);

            while (yield $records->advance()) {
                $filtered[] = $records->getCurrent()[0];
            }

            self::assertSame([1, 3, 5, 7, 9], $filtered);
        });
    }
}
