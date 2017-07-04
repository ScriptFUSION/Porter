<?php
namespace ScriptFUSIONTest\Integration\Porter;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Collection\RecordCollection;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\RecoverableConnectorException;
use ScriptFUSION\Porter\ImportException;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAware;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\ProviderNotFoundException;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;
use ScriptFUSION\Porter\Transform\FilterTransformer;
use ScriptFUSION\Porter\Transform\Transformer;
use ScriptFUSION\Retry\ExceptionHandler\ExponentialBackoffExceptionHandler;
use ScriptFUSION\Retry\FailingTooHardException;
use ScriptFUSIONTest\MockFactory;

final class PorterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Porter
     */
    private $porter;

    /**
     * @var Provider|MockInterface
     */
    private $provider;

    /**
     * @var ProviderResource
     */
    private $resource;

    /**
     * @var ImportSpecification
     */
    private $specification;

    /**
     * @var ContainerInterface|MockInterface
     */
    private $container;

    protected function setUp()
    {
        $this->porter = new Porter($this->container = $container = \Mockery::spy(ContainerInterface::class));

        $this->registerProvider(
            $this->provider =
                \Mockery::mock(Provider::class)
                    ->shouldReceive('getConnector')
                    ->andReturn(\Mockery::mock(Connector::class))
                    ->byDefault()
                    ->getMock()
        );

        $this->resource = MockFactory::mockResource($this->provider);
        $this->specification = new ImportSpecification($this->resource);
    }

    #region Import

    public function testImport()
    {
        $records = $this->porter->import($this->specification);

        self::assertInstanceOf(PorterRecords::class, $records);
        self::assertNotSame($this->specification, $records->getSpecification(), 'Specification was not cloned.');
        self::assertSame('foo', $records->current());

        /** @var ProviderRecords $previous */
        self::assertInstanceOf(ProviderRecords::class, $previous = $records->getPreviousCollection());
        self::assertNotSame($this->resource, $previous->getResource(), 'Resource was not cloned.');
    }

    /**
     * Tests that when the resource is countable the count is propagated to the outermost collection.
     */
    public function testImportCountableRecords()
    {
        $records = $this->porter->import(
            new StaticDataImportSpecification(new \ArrayIterator(range(1, $count = 10)))
        );

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $first = $records->findFirstCollection());
        self::assertCount($count, $first);

        // Outermost collection.
        self::assertInstanceOf(\Countable::class, $records);
        self::assertCount($count, $records);
    }

    /**
     * Tests that when the resource is countable the count is lost when filtering is applied.
     */
    public function testImportAndFilterCountableRecords()
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
     * Tests that when a Transformer is PorterAware it receives the Porter instance that invoked it.
     */
    public function testPorterAwareTransformer()
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
    public function testImportCustomProviderName()
    {
        $this->registerProvider(
            $provider = clone $this->provider,
            $providerName = 'foo'
        );

        $records = $this->porter->import(
            (new ImportSpecification(MockFactory::mockResource($provider, new \ArrayIterator([$output = 'bar']))))
                ->setProviderName($providerName)
        );

        self::assertSame($output, $records->current());
    }

    public function testImportFailure()
    {
        $this->resource->shouldReceive('fetch')->andReturn(null);

        $this->setExpectedException(ImportException::class, get_class($this->provider));
        $this->porter->import($this->specification);
    }

    public function testImportUnregisteredProvider()
    {
        $this->setExpectedException(ProviderNotFoundException::class);

        $this->porter->import((new ImportSpecification($this->resource))->setProviderName('foo'));
    }

    #endregion

    #region Import one

    public function testImportOne()
    {
        $result = $this->porter->importOne($this->specification);

        self::assertSame('foo', $result);
    }

    public function testImportOneOfNone()
    {
        $this->resource->shouldReceive('fetch')->andReturn(new \EmptyIterator);

        $result = $this->porter->importOne($this->specification);

        self::assertNull($result);
    }

    public function testImportOneOfMany()
    {
        $this->resource->shouldReceive('fetch')->andReturn(new \ArrayIterator(['foo', 'bar']));

        $this->setExpectedException(ImportException::class);
        $this->porter->importOne($this->specification);
    }

    #endregion

    #region Durability

    public function testOneTry()
    {
        $this->resource->shouldReceive('fetch')->once()->andThrow(RecoverableConnectorException::class);

        $this->setExpectedException(FailingTooHardException::class, '1');
        $this->porter->import($this->specification->setMaxFetchAttempts(1));
    }

    public function testDerivedRecoverableException()
    {
        $this->resource->shouldReceive('fetch')->once()->andThrow(\Mockery::mock(RecoverableConnectorException::class));

        $this->setExpectedException(FailingTooHardException::class);
        $this->porter->import($this->specification->setMaxFetchAttempts(1));
    }

    public function testDefaultTries()
    {
        $this->resource->shouldReceive('fetch')->times(ImportSpecification::DEFAULT_FETCH_ATTEMPTS)
            ->andThrow(RecoverableConnectorException::class);

        $this->setExpectedException(FailingTooHardException::class, (string)ImportSpecification::DEFAULT_FETCH_ATTEMPTS);
        $this->porter->import($this->specification);
    }

    public function testUnrecoverableException()
    {
        $this->resource->shouldReceive('fetch')->once()->andThrow(\Exception::class);

        $this->setExpectedException(\Exception::class);
        $this->porter->import($this->specification);
    }

    public function testCustomFetchExceptionHandler()
    {
        $this->specification->setFetchExceptionHandler(
            \Mockery::mock(ExponentialBackoffExceptionHandler::class)
                ->shouldReceive('__invoke')
                ->times(ImportSpecification::DEFAULT_FETCH_ATTEMPTS - 1)
                ->getMock()
        );
        $this->resource->shouldReceive('fetch')->times(ImportSpecification::DEFAULT_FETCH_ATTEMPTS)
            ->andThrow(RecoverableConnectorException::class);

        $this->setExpectedException(FailingTooHardException::class);
        $this->porter->import($this->specification);
    }

    /**
     * Tests that when a generator throws a recoverable exception before the first yield, the fetch is retried.
     *
     * Note this does not support cases where exceptions may be thrown in subsequent iterations.
     */
    public function testGeneratorException()
    {
        $this->resource->shouldReceive('fetch')->once()->andReturnUsing(function () {
            throw new RecoverableConnectorException;

            yield;
        });

        $this->setExpectedException(FailingTooHardException::class, '1');
        $this->porter->import($this->specification->setMaxFetchAttempts(1));
    }

    #endregion

    public function testFilter()
    {
        $this->resource->shouldReceive('fetch')->andReturn(new \ArrayIterator(range(1, 10)));

        $records = $this->porter->import(
            $this->specification
                ->addTransformer(new FilterTransformer($filter = function ($record) {
                    return $record % 2;
                }))
        );

        self::assertInstanceOf(PorterRecords::class, $records);
        self::assertSame([1, 3, 5, 7, 9], iterator_to_array($records));

        /** @var FilteredRecords $previous */
        self::assertInstanceOf(FilteredRecords::class, $previous = $records->getPreviousCollection());
        self::assertNotSame($previous->getFilter(), $filter, 'Filter was not cloned.');
    }

    public function testApplyCacheAdvice()
    {
        $this->provider->shouldReceive('getConnector')
            ->andReturn(
                \Mockery::mock(implode(',', [Connector::class, CacheToggle::class]))
                    ->shouldReceive('disableCache')->once()
                    ->shouldReceive('enableCache')->once()
                    ->getMock()
            )
        ;

        $this->porter->import($specification = new ImportSpecification($this->resource));
        $this->porter->import($specification->setCacheAdvice(CacheAdvice::SHOULD_CACHE()));
    }

    public function testCacheUnavailable()
    {
        $this->setExpectedException(CacheUnavailableException::class);

        $this->porter->import($this->specification->setCacheAdvice(CacheAdvice::MUST_CACHE()));
    }

    private function registerProvider(Provider $provider, $name = null)
    {
        $name = $name ?: get_class($provider);

        $this->container
            ->shouldReceive('has')->with($name)->andReturn(true)
            ->shouldReceive('get')->with($name)->andReturn($provider)
        ;
    }
}
