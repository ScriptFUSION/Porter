<?php
namespace ScriptFUSIONTest\Integration\Porter;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use ScriptFUSION\Mapper\CollectionMapper;
use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Cache\CacheToggle;
use ScriptFUSION\Porter\Cache\CacheUnavailableException;
use ScriptFUSION\Porter\Collection\CountableMappedRecords;
use ScriptFUSION\Porter\Collection\FilteredRecords;
use ScriptFUSION\Porter\Collection\MappedRecords;
use ScriptFUSION\Porter\Collection\PorterRecords;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\ImportException;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Provider\StaticDataProvider;
use ScriptFUSION\Porter\ProviderAlreadyRegisteredException;
use ScriptFUSION\Porter\ProviderNotFoundException;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;
use ScriptFUSIONTest\MockFactory;

final class PorterTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var Porter */
    private $porter;

    /** @var Provider|MockInterface */
    private $provider;

    /** @var ProviderResource */
    private $resource;

    /** @var ImportSpecification */
    private $specification;

    protected function setUp()
    {
        $this->porter = (new Porter)->registerProvider(
            $this->provider =
                \Mockery::mock(Provider::class)
                    ->shouldReceive('fetch')
                    ->andReturnUsing(function () {
                        yield 'foo';
                    })
                    ->byDefault()
                    ->getMock()
        );

        $this->resource = MockFactory::mockResource($this->provider);
        $this->specification = new ImportSpecification($this->resource);
    }

    #region Providers

    public function testGetProvider()
    {
        self::assertSame($this->provider, $this->porter->getProvider(get_class($this->provider)));
    }

    public function testRegisterSameProvider()
    {
        $this->setExpectedException(ProviderAlreadyRegisteredException::class);

        $this->porter->registerProvider($this->provider);
    }

    public function testRegisterSameProviderType()
    {
        $this->setExpectedException(ProviderAlreadyRegisteredException::class);

        $this->porter->registerProvider(clone $this->provider);
    }

    public function testRegisterProviderTag()
    {
        $this->porter->registerProvider($provider = clone $this->provider, 'foo');

        self::assertSame($provider, $this->porter->getProvider(get_class($this->provider), 'foo'));
    }

    public function testGetStaticProvider()
    {
        self::assertInstanceOf(StaticDataProvider::class, $this->porter->getProvider(StaticDataProvider::class));
    }

    public function testGetInvalidProvider()
    {
        $this->setExpectedException(ProviderNotFoundException::class);

        $this->porter->getProvider('foo');
    }

    public function testGetInvalidTag()
    {
        $this->setExpectedException(ProviderNotFoundException::class);

        $this->porter->getProvider(get_class($this->provider), 'foo');
    }

    public function testGetStaticProviderTag()
    {
        $this->setExpectedException(ProviderNotFoundException::class);

        $this->porter->getProvider(StaticDataProvider::class, 'foo');
    }

    public function testHasProvider()
    {
        self::assertTrue($this->porter->hasProvider(get_class($this->provider)));
        self::assertFalse($this->porter->hasProvider(get_class($this->provider), 'foo'));
        self::assertFalse($this->porter->hasProvider('foo'));
    }

    #endregion

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
     * Tests that when the resource is countable the count is propagated to the outermost collection via a mapped
     * collection.
     */
    public function testImportAndMapCountableRecords()
    {
        $records = $this->porter->import(
            (new StaticDataImportSpecification(
                new \ArrayIterator(range(1, $count = 10))
            ))->setMapping(\Mockery::mock(Mapping::class))
        );

        self::assertInstanceOf(CountableMappedRecords::class, $records->getPreviousCollection());
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
                new \ArrayIterator(range(1, $count = 10))
            ))->setFilter([$this, __FUNCTION__])
        );

        // Innermost collection.
        self::assertInstanceOf(\Countable::class, $records->findFirstCollection());

        // Outermost collection.
        self::assertNotInstanceOf(\Countable::class, $records);
    }

    public function testImportTaggedResource()
    {
        $this->porter->registerProvider(
            $provider = \Mockery::mock(Provider::class)
                ->shouldReceive('fetch')
                ->andReturn(new \ArrayIterator([$output = 'bar']))
                ->getMock(),
            $tag = 'foo'
        );

        $records = $this->porter->import(MockFactory::mockImportSpecification(
            MockFactory::mockResource($provider)
                ->shouldReceive('getProviderTag')
                ->andReturn($tag)
                ->getMock()
        ));

        self::assertSame($output, $records->current());
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
        $this->provider->shouldReceive('fetch')->andReturn(new \EmptyIterator);

        $result = $this->porter->importOne($this->specification);

        self::assertNull($result);
    }

    public function testImportOneOfMany()
    {
        $this->setExpectedException(ImportException::class);

        $this->provider->shouldReceive('fetch')->andReturn(new \ArrayIterator(['foo', 'bar']));

        $this->porter->importOne($this->specification);
    }

    #endregion

    public function testFilter()
    {
        $this->provider->shouldReceive('fetch')->andReturn(new \ArrayIterator(range(1, 10)));

        $records = $this->porter->import(
            $this->specification
                ->setFilter(function ($record) {
                    return $record % 2;
                })
        );

        self::assertInstanceOf(PorterRecords::class, $records);
        self::assertSame([1, 3, 5, 7, 9], iterator_to_array($records));

        /** @var FilteredRecords $previous */
        self::assertInstanceOf(FilteredRecords::class, $previous = $records->getPreviousCollection());
        self::assertNotSame($previous->getFilter(), $this->specification->getFilter(), 'Filter was not cloned.');
    }

    public function testMap()
    {
        $records = $this->porter->setMapper(
            \Mockery::mock(CollectionMapper::class)
                ->shouldReceive('mapCollection')
                ->with(\Mockery::type(\Iterator::class), $mapping = \Mockery::type(Mapping::class), \Mockery::any())
                ->once()
                ->andReturn(new \ArrayIterator($result = ['foo' => 'bar']))
                ->getMock()
        )->import($this->specification->setMapping(\Mockery::mock(Mapping::class)));

        self::assertInstanceOf(PorterRecords::class, $records);
        self::assertSame($result, iterator_to_array($records));

        /** @var MappedRecords $previous */
        self::assertInstanceOf(MappedRecords::class, $previous = $records->getPreviousCollection());
        self::assertNotSame($mapping, $previous->getMapping(), 'Mapping was not cloned.');
    }

    public function testApplyCacheAdvice()
    {
        $this->porter->registerProvider(
            $provider = \Mockery::mock(implode(',', [Provider::class, CacheToggle::class]))
                ->shouldReceive('fetch')->andReturn(new \EmptyIterator)
                ->shouldReceive('disableCache')->once()
                ->shouldReceive('enableCache')->once()
                ->getMock()
        );

        $this->porter->import($specification = new ImportSpecification(MockFactory::mockResource($provider)));
        $this->porter->import($specification->setCacheAdvice(CacheAdvice::SHOULD_CACHE()));
    }

    public function testCacheUnavailable()
    {
        $this->setExpectedException(CacheUnavailableException::class);

        $this->porter->import($this->specification->setCacheAdvice(CacheAdvice::MUST_CACHE()));
    }
}
