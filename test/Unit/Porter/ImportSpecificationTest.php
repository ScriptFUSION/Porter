<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Provider\DataSource\ProviderDataSource;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class ImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportSpecification */
    private $specification;

    /** @var ProviderDataSource */
    private $dataSource;

    protected function setUp()
    {
        $this->specification = new ImportSpecification(
            $this->dataSource = \Mockery::mock(ProviderDataSource::class)
        );
    }

    public function testClone()
    {
        $this->specification->setMapping($mapping = \Mockery::mock(Mapping::class))->setContext($context = (object)[]);
        $specification = clone $this->specification;

        self::assertNotSame($this->dataSource, $specification->getDataSource());
        self::assertNotSame($mapping, $specification->getMapping());
        self::assertNotSame($context, $specification->getContext());
    }

    public function testProviderData()
    {
        self::assertSame($this->dataSource, $this->specification->getDataSource());
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
            $filter = [$this, __FUNCTION__],
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
}
