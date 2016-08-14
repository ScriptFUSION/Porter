<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class ImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportSpecification */
    private $specification;

    /** @var Resource */
    private $resource;

    protected function setUp()
    {
        $this->specification = new ImportSpecification(
            $this->resource = \Mockery::mock(ProviderResource::class)
        );
    }

    public function testClone()
    {
        $this->specification->setMapping($mapping = \Mockery::mock(Mapping::class))->setContext($context = (object)[]);
        $specification = clone $this->specification;

        self::assertNotSame($this->resource, $specification->getResource());
        self::assertNotSame($mapping, $specification->getMapping());
        self::assertNotSame($context, $specification->getContext());
    }

    public function testProviderData()
    {
        self::assertSame($this->resource, $this->specification->getResource());
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
