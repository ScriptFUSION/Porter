<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Mapper\Mapping;
use ScriptFUSION\Porter\ObjectFinalizedException;
use ScriptFUSION\Porter\Provider\ProviderDataType;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class ImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportSpecification */
    private $specification;

    /** @var ProviderDataType */
    private $providerDataType;

    protected function setUp()
    {
        $this->specification = new ImportSpecification(
            $this->providerDataType = \Mockery::mock(ProviderDataType::class)
        );
    }

    public function testFinalize()
    {
        self::assertFalse($this->specification->isFinalized());

        $this->specification->finalize();

        self::assertTrue($this->specification->isFinalized());
    }

    public function testFinalizeAugmentation()
    {
        $this->setExpectedException(ObjectFinalizedException::class);

        $this->specification->finalize();
        $this->specification->setContext('foo');
    }

    public function testProviderData()
    {
        self::assertSame($this->providerDataType, $this->specification->getProviderDataType());
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
}
