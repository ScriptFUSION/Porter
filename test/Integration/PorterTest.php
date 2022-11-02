<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Async\Throttle\DualThrottle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSION\Porter\ForeignResourceException;
use ScriptFUSION\Porter\ImportException;
use ScriptFUSION\Porter\IncompatibleResourceException;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderFactory;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;
use ScriptFUSION\Porter\ProviderNotFoundException;
use ScriptFUSION\Porter\Specification\Specification;
use ScriptFUSION\Porter\Specification\StaticDataSpecification;
use ScriptFUSION\Porter\Transform\FilterTransformer;
use ScriptFUSION\Porter\Transform\Transformer;
use ScriptFUSION\Retry\FailingTooHardException;
use ScriptFUSIONTest\MockFactory;
use ScriptFUSIONTest\Stubs\TestRecoverableException;
use ScriptFUSIONTest\Stubs\TestRecoverableExceptionHandler;
use function Amp\async;
use function Amp\delay;
use function Amp\Future\await;

final class PorterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Porter $porter;
    private Provider|MockInterface $provider;
    private ProviderResource|MockInterface $resource;
    private ProviderResource|MockInterface $singleResource;
    private Connector|MockInterface $connector;
    private Specification $specification;
    private Specification $singleSpecification;
    private ContainerInterface|MockInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->porter = new Porter($this->container = \Mockery::spy(ContainerInterface::class));

        $this->registerProvider($this->provider = MockFactory::mockProvider());
        $this->connector = $this->provider->getConnector();
        $this->resource = MockFactory::mockResource($this->provider);
        $this->specification = new Specification($this->resource);
        $this->singleResource = MockFactory::mockSingleRecordResource($this->provider);
        $this->singleSpecification = new Specification($this->singleResource);
    }

    private function registerProvider(Provider $provider, string $name = null): void
    {
        $name ??= \get_class($provider);

        $this->container
            ->shouldReceive('has')->with($name)->andReturn(true)
            ->shouldReceive('get')->with($name)->andReturn($provider)->byDefault()
        ;
    }

    /**
     * Arranges for the current connector to throw an exception in the retry callback.
     */
    private function arrangeConnectorException(\Exception $exception): void
    {
        $this->connector->shouldReceive('fetch')->andThrow($exception);
    }

    #region Import

    /**
     * Tests that the full import path, via connector, resource and provider, fetches a record correctly.
     */
    public function testImport(): void
    {
        $records = $this->porter->import($this->specification);

        self::assertInstanceOf(PorterRecords::class, $records);
        self::assertNotSame($this->specification, $records->getSpecification(), 'Specification was not cloned.');
        self::assertSame(['foo'], $records->current());

        /** @var ProviderRecords $previous */
        self::assertInstanceOf(ProviderRecords::class, $previous = $records->getPreviousCollection());
        self::assertNotSame($this->resource, $previous->getResource(), 'Resource was not cloned.');
    }

    /**
     * Tests that when the resource is countable, the count is propagated to the outermost collection.
     */
    public function testImportCountableRecords(): void
    {
        $records = $this->porter->import(
            new StaticDataSpecification(new \ArrayIterator(range(1, $count = 10)))
        );

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $first = $records->findFirstCollection());
        self::assertCount($count, $first);

        // Outermost collection.
        self::assertInstanceOf(CountablePorterRecords::class, $records);
        self::assertCount($count, $records);
    }

    /**
     * Tests that when the resource is countable the count is lost when filtering is applied.
     */
    public function testImportAndFilterCountableRecords(): void
    {
        $records = $this->porter->import(
            (new StaticDataSpecification(
                new \ArrayIterator(array_map(fn ($i) => [$i], range(1, 10)))
            ))->addTransformer(new FilterTransformer(fn () => true))
        );

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $records->findFirstCollection());

        // Outermost collection.
        self::assertNotInstanceOf(\Countable::class, $records);
    }

    /**
     * Tests that when importing multiple records, records may be rewound when the iterator supports this.
     */
    public function testRewind(): void
    {
        $this->resource->shouldReceive('fetch')->andReturn(new \ArrayIterator([$i1 = ['foo'], $i2 = ['bar']]));

        $records = $this->porter->import($this->specification);

        self::assertTrue($records->valid());
        self::assertCount(2, $records);
        self::assertSame($i1, $records->current());
        $records->next();
        self::assertSame($i2, $records->current());
        $records->rewind();
        self::assertSame($i1, $records->current());
    }

    /**
     * Tests that when importing records implemented using deferred execution with generators, the generator runs up
     * to the first suspension point instead of being paused at the start.
     */
    public function testImportGenerator(): void
    {
        $this->resource->expects('fetch')->andReturnUsing(function () use (&$init): \Generator {
            $init = true;

            yield [];
        });

        $this->porter->import($this->specification);

        self::assertTrue($init);
    }

    /**
     * Tests that when a Transformer is PorterAware it receives the Porter instance that invoked it.
     */
    public function testPorterAwareTransformer(): void
    {
        $this->porter->import(
            $this->specification->addTransformer(
                \Mockery::mock(implode(',', [Transformer::class, PorterAware::class]))
                    ->shouldReceive('setPorter')
                        ->with($this->porter)
                        ->once()
                    ->shouldReceive('transform')
                        ->andReturn(\Mockery::spy(RecordCollection::class))
                    ->getMock()
            )
        );
    }

    /**
     * Tests that when provider name is specified in an import specification its value is used instead of the default
     * provider class name of the resource.
     */
    public function testImportCustomProviderName(): void
    {
        $this->registerProvider(
            $provider = clone $this->provider,
            $providerName = 'foo'
        );

        $records = $this->porter->import(
            (new Specification(MockFactory::mockResource($provider, new \ArrayIterator([$output = ['bar']]))))
                ->setProviderName($providerName)
        );

        self::assertSame($output, $records->current());
    }

    /**
     * Tests that when a resource does not return an iterator, an exception is thrown.
     */
    public function testImportFailure(): void
    {
        $this->resource->shouldReceive('fetch')->andReturn(null);

        $this->expectException(\TypeError::class);
        $this->expectExceptionMessage(\get_class($this->resource));
        $this->porter->import($this->specification);
    }

    public function testImportUnregisteredProvider(): void
    {
        $this->expectException(ProviderNotFoundException::class);
        $this->expectExceptionMessage($providerName = 'foo');
        $this->expectExceptionCode(0);

        $this->porter->import($this->specification->setProviderName("\"$providerName\""));
    }

    /**
     * Tests that when a resource's provider class name does not match the provider an exception is thrown.
     */
    public function testImportForeignResource(): void
    {
        // Replace existing provider with a different one.
        $this->registerProvider(MockFactory::mockProvider(), \get_class($this->provider));

        $this->expectException(ForeignResourceException::class);
        $this->porter->import($this->specification);
    }

    /**
     * Tests that when importing a single record resource, an exception is thrown.
     */
    public function testImportSingle(): void
    {
        $this->expectException(IncompatibleResourceException::class);
        $this->expectExceptionMessage('importOne()');

        $this->porter->import($this->singleSpecification);
    }

    /**
     * Tests that when a resource returns ProviderRecords, Porter does not wrap the collection again.
     */
    public function testProviderRecordsNotDoubleWrapped(): void
    {
        $this->resource->shouldReceive('fetch')
            ->andReturn($records = new ProviderRecords(new \ArrayIterator([]), $this->resource));

        $imported = $this->porter->import($this->specification);

        self::assertInstanceOf(PorterRecords::class, $imported);
        self::assertSame($records, $imported->getPreviousCollection());
    }

    #endregion

    #region Import one

    public function testImportOne(): void
    {
        $result = $this->porter->importOne($this->singleSpecification);

        self::assertSame(['foo'], $result);
    }

    public function testImportOneOfNone(): void
    {
        $this->singleResource->shouldReceive('fetch')->andReturn(new \EmptyIterator);

        $result = $this->porter->importOne($this->singleSpecification);

        self::assertNull($result);
    }

    public function testImportOneOfMany(): void
    {
        $this->singleResource->shouldReceive('fetch')->andReturn(new \ArrayIterator([['foo'], ['bar']]));

        $this->expectException(ImportException::class);
        $this->porter->importOne($this->singleSpecification);
    }

    /**
     * Tests that when importing one from a resource not marked with SingleRecordResource, an exception is thrown.
     */
    public function testImportOneNonSingle(): void
    {
        $this->expectException(IncompatibleResourceException::class);
        $this->expectExceptionMessage(SingleRecordResource::class);

        $this->porter->importOne(new Specification(\Mockery::mock(ProviderResource::class)));
    }

    #endregion

    #region Durability

    /**
     * Tests that when a connector throws the recoverable exception type, the connection attempt is retried once.
     */
    public function testOneTry(): void
    {
        $this->arrangeConnectorException(new TestRecoverableException);

        $this->expectException(FailingTooHardException::class);
        $this->expectExceptionMessage('1');
        $this->porter->import($this->specification->setMaxFetchAttempts(1));
    }

    /**
     * Tests that when a connector throws an exception type derived from the recoverable exception type, the connection
     * is retried.
     */
    public function testDerivedRecoverableException(): void
    {
        $this->arrangeConnectorException(new TestRecoverableException);

        $this->expectException(FailingTooHardException::class);
        $this->porter->import($this->specification->setMaxFetchAttempts(1));
    }

    /**
     * Tests that when a connector throws the recoverable exception type, the connection can be retried the default
     * number of times (more than once).
     */
    public function testDefaultTries(): void
    {
        $this->arrangeConnectorException(new TestRecoverableException);
        // Speed up test by circumventing exponential backoff default handler.
        $this->specification->setRecoverableExceptionHandler(new TestRecoverableExceptionHandler);

        $this->expectException(FailingTooHardException::class);
        $this->expectExceptionMessage((string)Specification::DEFAULT_FETCH_ATTEMPTS);
        $this->porter->import($this->specification);
    }

    /**
     * Tests that when a connector throws a non-recoverable exception type, the connection is not retried.
     */
    public function testUnrecoverableException(): void
    {
        // Subclass Exception so it's not an ancestor of any other exception.
        $this->arrangeConnectorException($exception = \Mockery::mock(\Exception::class));

        $this->expectException(\get_class($exception));
        $this->porter->import($this->specification);
    }

    /**
     * Tests that when a custom fetch exception handler is specified and the connector throws a recoverable exception
     * type, the handler is called on each retry.
     */
    public function testCustomFetchExceptionHandler(): void
    {
        $this->specification->setRecoverableExceptionHandler(
            \Mockery::mock(RecoverableExceptionHandler::class)
                ->shouldReceive('initialize')
                    ->once()
                ->shouldReceive('__invoke')
                    ->times(Specification::DEFAULT_FETCH_ATTEMPTS - 1)
                ->getMock()
        );

        $this->arrangeConnectorException(new TestRecoverableException);

        $this->expectException(FailingTooHardException::class);
        $this->porter->import($this->specification);
    }

    /**
     * Tests that when a provider fetch exception handler is specified and the connector throws a recoverable
     * exception, the handler is called before the user handler.
     */
    public function testCustomProviderFetchExceptionHandler(): void
    {
        $this->specification->setRecoverableExceptionHandler(
            new StatelessRecoverableExceptionHandler(static function (): void {
                throw new \LogicException('This exception must not be thrown!');
            })
        );

        $this->arrangeConnectorException($connectorException =
            new TestRecoverableException('This exception is caught by the provider handler.'));

        $this->resource
            ->shouldReceive('fetch')
            ->andReturnUsing(static function (ImportConnector $connector) use ($connectorException): \Generator {
                $connector->setRecoverableExceptionHandler(new StatelessRecoverableExceptionHandler(
                    static function (\Exception $exception) use ($connectorException) {
                        self::assertSame($connectorException, $exception);

                        throw new \RuntimeException('This exception is thrown by the provider handler.');
                    }
                ));

                yield $connector->fetch(\Mockery::mock(DataSource::class));
            })
        ;

        $this->expectException(\RuntimeException::class);
        $this->porter->importOne($this->singleSpecification);
    }

    #endregion

    public function testFilter(): void
    {
        $this->resource->shouldReceive('fetch')->andReturnUsing(
            static function (): \Generator {
                foreach (range(1, 10) as $integer) {
                    yield [$integer];
                }
            }
        );

        $records = $this->porter->import(
            $this->specification
                ->addTransformer(new FilterTransformer($filter = static function (array $record): int {
                    return $record[0] % 2;
                }))
        );

        self::assertInstanceOf(PorterRecords::class, $records);
        self::assertSame([[1], [3], [5], [7], [9]], iterator_to_array($records));

        /** @var FilteredRecords $previous */
        self::assertInstanceOf(FilteredRecords::class, $previous = $records->getPreviousCollection());
        self::assertNotSame($previous->getFilter(), $filter, 'Filter was not cloned.');
    }

    /**
     * Tests that when caching is required but a caching facility is unavailable, an exception is thrown.
     */
    public function testCacheUnavailable(): void
    {
        $this->expectException(CacheUnavailableException::class);

        $this->porter->import($this->specification->enableCache());
    }

    #region Throttle

    /**
     * Tests that a working throttle implementation is invoked during fetch operations.
     */
    public function testThrottle(): void
    {
        $this->specification->setThrottle($throttle = new DualThrottle);
        $throttle->setMaxConcurrency(1);

        $records = async($this->porter->import(...), $this->specification);
        delay(0);
        self::assertTrue($throttle->isThrottling());

        $records->await();
        self::assertFalse($throttle->isThrottling());
    }

    /**
     * Tests that a working throttle implementation can be called from multiple fibers queueing excess operations.
     */
    public function testThrottleConcurrentFibers(): void
    {
        $this->specification->setThrottle($throttle = new DualThrottle);
        $throttle->setMaxPerSecond(1);

        $import = fn () => async($this->porter->import(...), $this->specification)->await();

        $start = microtime(true);
        await([async($import), async($import), async($import)]);

        self::assertGreaterThan(3, microtime(true) - $start);
    }

    #endregion

    /**
     * Tests that when a provider is fetched from the provider factory multiple times, the provider factory is only
     * created once.
     */
    public function testGetOrCreateProviderFactory(): void
    {
        $property = new \ReflectionProperty($this->porter, 'providerFactory');

        $this->porter->import($spec = new StaticDataSpecification(new \EmptyIterator()));
        self::assertInstanceOf(ProviderFactory::class, $factory1 = $property->getValue($this->porter));

        $this->porter->import($spec);
        self::assertInstanceOf(ProviderFactory::class, $factory2 = $property->getValue($this->porter));

        self::assertSame($factory1, $factory2);
    }
}
