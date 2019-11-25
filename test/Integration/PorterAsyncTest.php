<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration;

use Amp\Iterator;
use Amp\Loop;
use Amp\Producer;
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
    public function testImportAsync(): \Generator
    {
        $records = $this->porter->importAsync($this->specification);

        self::assertTrue(yield $records->advance());
        self::assertSame(['foo'], $records->getCurrent());
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
     * Tests that the full async import path, via connector, resource and provider, fetches one record correctly.
     */
    public function testImportOneAsync(): \Generator
    {
        self::assertSame(['foo'], yield $this->porter->importOneAsync($this->singleSpecification));
    }

    /**
     * Tests that when importing one from a resource not marked with SingleRecordResource, an exception is thrown.
     */
    public function testImportOneNonSingleAsync(): \Generator
    {
        $this->expectException(IncompatibleResourceException::class);
        $this->expectExceptionMessage(SingleRecordResource::class);

        yield $this->porter->importOneAsync(new AsyncImportSpecification(\Mockery::mock(AsyncResource::class)));
    }

    /**
     * Tests that when the resource is countable, the count is propagated to the outermost collection and the records
     * are intact.
     */
    public function testImportCountableAsyncRecords(): \Generator
    {
        $this->resource->shouldReceive('fetchAsync')->andReturn(
            new CountableAsyncProviderRecords(Iterator\fromIterable([$record = ['foo']]), $count = 123, $this->resource)
        );

        $records = $this->porter->importAsync($this->specification);

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $first = $records->findFirstCollection());
        self::assertCount($count, $first);

        // Outermost collection.
        self::assertInstanceOf(CountableAsyncPorterRecords::class, $records);
        self::assertCount($count, $records);

        self::assertTrue(yield $records->advance());
        self::assertSame($record, $records->getCurrent());
    }

    /**
     * Tests that when importOne receives multiple records from a resource, an exception is thrown.
     */
    public function testImportOneOfManyAsync(): \Generator
    {
        $this->singleResource->shouldReceive('fetchAsync')->andReturn(Iterator\fromIterable([['foo'], ['bar']]));

        $this->expectException(ImportException::class);
        yield $this->porter->importOneAsync($this->singleSpecification);
    }

    /**
     * Tests that when importing from a provider that does not implement AsyncProvider, an exception is thrown.
     */
    public function testImportIncompatibleProviderAsync(): \Generator
    {
        $this->registerProvider(\Mockery::mock(Provider::class), $providerName = 'foo');

        $this->expectException(IncompatibleProviderException::class);
        $this->expectExceptionMessageRegExp('[\bAsyncProvider\b]');
        yield $this->porter->importAsync($this->specification->setProviderName($providerName));
    }

    /**
     * Tests that when a resource's provider class name does not match the provider an exception is thrown.
     */
    public function testImportForeignResourceAsync(): \Generator
    {
        // Replace existing provider with a different one.
        $this->registerProvider(MockFactory::mockProvider(), \get_class($this->provider));

        $this->expectException(ForeignResourceException::class);
        yield $this->porter->importAsync($this->specification);
    }

    /**
     * Tests that a stack of async filter transformers are applied correctly.
     * The order is deterministic because filters yield immediately.
     */
    public function testFilterAsync(): void
    {
        $this->resource->shouldReceive('fetchAsync')->andReturnUsing(static function (): Iterator {
            return new Producer(static function (\Closure $emit): \Generator {
                foreach (range(1, 10) as $integer) {
                    yield $emit([$integer]);
                }
            });
        });

        // Filter out even numbers.
        $this->specification->addTransformer(
            new FilterTransformer(static function (array $record): int {
                return $record[0] % 2;
            })
        );

        $importAndExpect = function ($expect): void {
            Loop::run(function () use ($expect): \Generator {
                $records = $this->porter->importAsync($this->specification);

                while (yield $records->advance()) {
                    $filtered[] = $records->getCurrent()[0];
                }

                self::assertSame($expect, $filtered);
            });
        };

        $importAndExpect([1, 3, 5, 7, 9]);

        // Filter out numbers below 6.
        $this->specification->addTransformer(
            new FilterTransformer(static function (array $record): bool {
                return $record[0] > 5;
            })
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
                        ->andReturn(\Mockery::mock(AsyncRecordCollection::class))
                    ->getMock()
            )
        );
    }
}
