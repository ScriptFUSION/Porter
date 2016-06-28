<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Specification\ObjectFinalizedException;
use ScriptFUSION\Porter\Provider\ProviderDataFetcher;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class ImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportSpecification */
    private $specification;

    /** @var ProviderDataFetcher */
    private $dataFetcher;

    protected function setUp()
    {
        $this->specification = new ImportSpecification(
            $this->dataFetcher = \Mockery::mock(ProviderDataFetcher::class)
        );
    }

    public function testFinalize()
    {
        self::assertFalse($this->specification->isFinalized());

        $this->specification->finalize();

        self::assertTrue($this->specification->isFinalized());

        // TODO: Test embedded objects are cloned.
    }

    public function testFinalizeAugmentation()
    {
        $this->setExpectedException(ObjectFinalizedException::class);

        $this->specification->finalize();
        $this->specification->setContext('foo');
    }

    public function testProviderData()
    {
        self::assertSame($this->dataFetcher, $this->specification->getDataFetcher());
    }

    public function testMapping()
    {
        self::assertSame(
            $mapping = \Mockery::mock(Mapping::class),
            $this->specification->setMapping($mapping)->getMapping()
        );
    }

    public function testContext()
    {
        self::assertSame('foo', $this->specification->setContext('foo')->getContext());
    }

    public function testFilter()
    {
        self::assertSame(
            $filter = function () {
                // Intentionally empty.
            },
            $this->specification->setFilter($filter)->getFilter()
        );
    }

    public function testCacheAdvice()
    {
        self::assertSame(
            $advice = CacheAdvice::MUST_CACHE(),
            $this->specification->setCacheAdvice($advice)->getCacheAdvice()
        );
    }

    public function testCreateFrom()
    {
        $specification = ImportSpecification::createFrom(
            $this->specification
                ->setMapping(
                    /** @var Mapping $mapping */
                    $mapping = \Mockery::mock(Mapping::class)
                        ->shouldReceive('getArrayCopy')
                        ->andReturn([range(1, 5)])
                        ->getMock()
                )
                ->setContext($context = 'foo')
                ->setFilter($filter = function () {
                    // Intentionally empty.
                })
        );

        self::assertNotSame($specification, $this->specification);
        self::assertNotSame($this->dataFetcher, $specification->getDataFetcher());
        self::assertNotSame($mapping, $specification->getMapping());
        self::assertSame($mapping->getArrayCopy(), $specification->getMapping()->getArrayCopy());
        self::assertSame($context, $specification->getContext());
        self::assertSame($filter, $specification->getFilter());
    }
}
