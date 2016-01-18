<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Porter\ImportSpecification;
use ScriptFUSION\Porter\Mapping\Mapping;
use ScriptFUSION\Porter\ObjectFinalizedException;
use ScriptFUSION\Porter\Provider\ProviderDataType;

final class ImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportSpecification */
    private $specification;

    /** @var ProviderDataType */
    private $dataType;

    protected function setUp()
    {
        $this->specification = new ImportSpecification($this->dataType = \Mockery::mock(ProviderDataType::class));
    }

    public function testFinalize()
    {
        $this->assertFalse($this->specification->isFinalized());

        $this->specification->finalize();

        $this->assertTrue($this->specification->isFinalized());
    }

    public function testFinalizeAugmentation()
    {
        $this->setExpectedException(ObjectFinalizedException::class);

        $this->specification->finalize();
        $this->specification->setProviderDataType($this->dataType);
    }

    public function testProviderDataType()
    {
        $this->assertSame($this->dataType, $this->specification->getProviderDataType());
    }

    public function testParameters()
    {
        $this->assertSame([], $this->specification->getParameters());

        $this->assertSame(
            $parameters = ['foo', 'bar'],
            $this->specification->setParameters($parameters)->getParameters()
        );
    }

    public function testMapping()
    {
        $this->assertSame(
            $mapping = \Mockery::mock(Mapping::class),
            $this->specification->setMapping($mapping)->getMapping()
        );
    }

    public function testContext()
    {
        $this->assertSame('foo', $this->specification->setContext('foo')->getContext());
    }

    public function testFilter()
    {
        $this->assertSame(
            $filter = function () {
                // Intentionally empty.
            },
            $this->specification->setFilter($filter)->getFilter()
        );
    }
}
