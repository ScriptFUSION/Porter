<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter;

use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\CountablePorterRecords;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\ConnectorOptions;
use ScriptFUSION\Porter\Connector\ImportConnector;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\StatelessRecoverableExceptionHandler;
use ScriptFUSION\Porter\ForeignResourceException;
use ScriptFUSION\Porter\ImportException;
use ScriptFUSION\Porter\IncompatibleProviderException;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\Provider\AsyncProvider;
use ScriptFUSION\Porter\ProviderNotFoundException;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;
use ScriptFUSION\Porter\Transform\FilterTransformer;
use ScriptFUSION\Porter\Transform\Transformer;
use ScriptFUSION\Retry\FailingTooHardException;
use ScriptFUSIONTest\MockFactory;
use ScriptFUSIONTest\Stubs\TestRecoverableException;

final class PorterSyncTest extends PorterTest
{
    #region Import

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
            new StaticDataImportSpecification(new \ArrayIterator(range(1, $count = 10)))
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
            (new StaticDataImportSpecification(
                new \ArrayIterator(range(1, 10))
            ))->addTransformer(new FilterTransformer([$this, __FUNCTION__]))
        );

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $records->findFirstCollection());

        // Outermost collection.
        self::assertNotInstanceOf(\Countable::class, $records);
    }

    /**
     * Tests that when importing using a connector that exports options, but no clone method, an exception is thrown.
     */
    public function testImportConnectorWithOptions(): void
    {
        $this->provider->shouldReceive('getConnector')
            ->andReturn(\Mockery::mock(Connector::class, ConnectorOptions::class));

        $this->expectException(\LogicException::class);
        $this->porter->import($this->specification);
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
                        ->andReturn(\Mockery::mock(RecordCollection::class))
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
            (new ImportSpecification(MockFactory::mockResource($provider, new \ArrayIterator([$output = ['bar']]))))
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

        $this->porter->import($this->specification->setProviderName('foo'));
    }

    /**
     * Tests that when importing from a provider that does not implement Provider, an exception is thrown.
     */
    public function testImportIncompatibleProvider(): void
    {
        $this->registerProvider(\Mockery::mock(AsyncProvider::class), $providerName = 'foo');

        $this->expectException(IncompatibleProviderException::class);
        $this->porter->import($this->specification->setProviderName($providerName));
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

    #endregion

    #region Import one

    public function testImportOne(): void
    {
        $result = $this->porter->importOne($this->specification);

        self::assertSame(['foo'], $result);
    }

    public function testImportOneOfNone(): void
    {
        $this->resource->shouldReceive('fetch')->andReturn(new \EmptyIterator);

        $result = $this->porter->importOne($this->specification);

        self::assertNull($result);
    }

    public function testImportOneOfMany(): void
    {
        $this->resource->shouldReceive('fetch')->andReturn(new \ArrayIterator([['foo'], ['bar']]));

        $this->expectException(ImportException::class);
        $this->porter->importOne($this->specification);
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

        $this->expectException(FailingTooHardException::class);
        $this->expectExceptionMessage((string)ImportSpecification::DEFAULT_FETCH_ATTEMPTS);
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
                    ->times(ImportSpecification::DEFAULT_FETCH_ATTEMPTS - 1)
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
                    function (\Exception $exception) use ($connectorException) {
                        self::assertSame($connectorException, $exception);

                        throw new \RuntimeException('This exception is thrown by the provider handler.');
                    }
                ));

                yield $connector->fetch('foo');
            })
        ;

        $this->expectException(\RuntimeException::class);
        $this->porter->importOne($this->specification);
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
}
