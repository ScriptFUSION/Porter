<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration;

use Amp\Future;
use ScriptFUSION\Async\Throttle\DualThrottle;
use ScriptFUSION\Porter\Collection\AsyncPorterRecords;
use ScriptFUSION\Porter\Collection\AsyncRecordCollection;
use ScriptFUSION\Porter\Collection\CountableAsyncPorterRecords;
use ScriptFUSION\Porter\Collection\CountableAsyncProviderRecords;
use ScriptFUSION\Porter\ForeignResourceException;
use ScriptFUSION\Porter\ImportException;
use ScriptFUSION\Porter\IncompatibleProviderException;
use ScriptFUSION\Porter\IncompatibleResourceException;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Transform\AsyncTransformer;
use ScriptFUSION\Porter\Transform\FilterTransformer;
use ScriptFUSIONTest\MockFactory;
use function Amp\async;
use function Amp\delay;

/**
 * @see Porter
 */
final class PorterAsyncTest extends PorterTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->specification = new AsyncImportSpecification($this->resource);
        $this->singleSpecification = new AsyncImportSpecification($this->singleResource);
    }

    /**
     * Tests that the full async import path, via connector, resource and provider, fetches a record correctly.
     */
    public function testImportAsync(): void
    {
        $records = $this->porter->importAsync($this->specification);

        $this->specification->setThrottle(new DualThrottle());
        self::assertInstanceOf(AsyncPorterRecords::class, $records);
        self::assertNotSame($this->specification, $records->getSpecification(), 'Specification was not cloned.');
        self::assertTrue($records->valid());
        self::assertSame(['foo'], $records->current());
    }

    /**
     * Tests that when importing a single record resource, an exception is thrown.
     */
    public function testImportSingle(): void
    {
        $this->expectException(IncompatibleResourceException::class);
        $this->expectExceptionMessage('importOneAsync()');

        $this->porter->importAsync($this->singleSpecification);
    }

    /**
     * Tests that when importing records implemented using deferred execution with generators, the generator runs up
     * to the first suspension point instead of being paused at the start.
     */
    public function testImportGenerator(): void
    {
        $this->resource->expects('fetchAsync')->andReturnUsing(function () use (&$init): \Generator {
            $init = true;

            yield [];
        });

        $this->porter->importAsync($this->specification);

        self::assertTrue($init);
    }

    /**
     * Tests that the full async import path, via connector, resource and provider, fetches one record correctly.
     */
    public function testImportOneAsync(): void
    {
        self::assertSame(['foo'], $this->porter->importOneAsync($this->singleSpecification));
    }

    /**
     * Tests that when importing one from a resource not marked with SingleRecordResource, an exception is thrown.
     */
    public function testImportOneNonSingleAsync(): void
    {
        $this->expectException(IncompatibleResourceException::class);
        $this->expectExceptionMessage(SingleRecordResource::class);

        $this->porter->importOneAsync(new AsyncImportSpecification(\Mockery::mock(AsyncResource::class)));
    }

    /**
     * Tests that when the resource is countable, the count is propagated to the outermost collection and the records
     * are intact.
     */
    public function testImportCountableAsyncRecords(): void
    {
        $this->resource->shouldReceive('fetchAsync')->andReturn(
            new CountableAsyncProviderRecords(new \ArrayIterator([$record = ['foo']]), $count = 123, $this->resource)
        );

        $records = $this->porter->importAsync($this->specification);

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $first = $records->findFirstCollection());
        self::assertCount($count, $first);

        // Outermost collection.
        self::assertInstanceOf(CountableAsyncPorterRecords::class, $records);
        self::assertCount($count, $records);

        self::assertTrue($records->valid());
        self::assertSame($record, $records->current());
    }

    /**
     * Tests that when importOne receives multiple records from a resource, an exception is thrown.
     */
    public function testImportOneOfManyAsync(): void
    {
        $this->singleResource->shouldReceive('fetchAsync')->andReturn(new \ArrayIterator([['foo'], ['bar']]));

        $this->expectException(ImportException::class);
        $this->porter->importOneAsync($this->singleSpecification);
    }

    /**
     * Tests that when importing from a provider that does not implement AsyncProvider, an exception is thrown.
     */
    public function testImportIncompatibleProviderAsync(): void
    {
        $this->registerProvider(\Mockery::mock(Provider::class), $providerName = 'foo');

        $this->expectException(IncompatibleProviderException::class);
        $this->expectExceptionMessageMatches('[\bAsyncProvider\b]');
        $this->porter->importAsync($this->specification->setProviderName($providerName));
    }

    /**
     * Tests that when a resource's provider class name does not match the provider an exception is thrown.
     */
    public function testImportForeignResourceAsync(): void
    {
        // Replace existing provider with a different one.
        $this->registerProvider(MockFactory::mockProvider(), \get_class($this->provider));

        $this->expectException(ForeignResourceException::class);
        $this->porter->importAsync($this->specification);
    }

    /**
     * Tests that a stack of async filter transformers are applied correctly.
     * The order is deterministic because filters yield immediately.
     */
    public function testFilterAsync(): void
    {
        $this->resource->shouldReceive('fetchAsync')->andReturnUsing(
            fn () => yield from array_map(static fn (int $i): array => [$i], range(1, 10))
        );

        // Filter out even numbers.
        $this->specification->addTransformer(
            new FilterTransformer(fn (array $record) => $record[0] % 2)
        );

        $importAndExpect = function ($expect): void {
            $records = $this->porter->importAsync($this->specification);

            $filtered = array_map(static fn (array $record): int => $record[0], iterator_to_array($records));

            self::assertSame($expect, $filtered);
        };

        $importAndExpect([1, 3, 5, 7, 9]);

        // Filter out numbers below 6.
        $this->specification->addTransformer(
            new FilterTransformer(fn (array $record) => $record[0] > 5)
        );

        $importAndExpect([7, 9]);
    }

    /**
     * Tests that when an AsyncTransformer is PorterAware it receives the Porter instance that invoked it.
     */
    public function testPorterAwareAsyncTransformer(): void
    {
        $this->porter->importAsync(
            $this->specification->addTransformer(
                \Mockery::mock(implode(',', [AsyncTransformer::class, PorterAware::class]))
                    ->shouldReceive('setPorter')
                        ->with($this->porter)
                        ->once()
                    ->shouldReceive('transformAsync')
                        ->andReturn(\Mockery::spy(AsyncRecordCollection::class))
                    ->getMock()
            )
        );
    }

    /**
     * Tests that a working throttle implementation is invoked during fetch operations.
     */
    public function testThrottle(): void
    {
        $this->specification->setThrottle($throttle = new DualThrottle);
        $throttle->setMaxConcurrency(1);

        $records = async($this->porter->importAsync(...), $this->specification);
        delay(0);
        self::assertTrue($throttle->isThrottling());

        $records->await();
        self::assertFalse($throttle->isThrottling());
    }

    /**
     * Tests that a working throttle implementation can be called from multiple fibers queueing excess objects.
     */
    public function testThrottleConcurrentFibers(): void
    {
        $this->specification->setThrottle($throttle = new DualThrottle);
        $throttle->setMaxPerSecond(1);

        $import = function (): void {
            $records = async($this->porter->importAsync(...), $this->specification)->await();
            delay(0);

            while ($records->valid()) {
                $records->next();
            }
        };

        $start = microtime(true);

        Future\await([async($import), async($import), async($import)]);

        self::assertGreaterThan(3, microtime(true) - $start);
    }
}
