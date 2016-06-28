<?php
namespace ScriptFUSIONTest\Unit\Porter\Mapper\Strategy;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Mapper\Strategy\InvalidCallbackResultException;
use ScriptFUSION\Porter\Mapper\Strategy\SubImport;
use ScriptFUSION\Porter\Porter;
use ScriptFUSIONTest\MockFactory;

/**
 * @see SubImport
 */
final class SubImportTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var MockInterface|SubImport */
    private $subImport;

    /** @var MockInterface|Porter */
    private $porter;

    protected function setUp()
    {
        $this->createSubImport();
    }

    public function testInvalidCreate()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->createSubImport(true);
    }

    public function testImport()
    {
        self::assertNull($this->import());

        $this->porter->shouldReceive('import')->andReturn(new \ArrayIterator($array = range(1, 5)));
        self::assertSame($array, $this->import());
    }

    public function testSpecificationCallback()
    {
        $this->createSubImport(
            function ($data, $context) {
                self::assertSame('foo', $data);
                self::assertSame('bar', $context);

                return MockFactory::mockImportSpecification();
            }
        );

        $this->import('foo', 'bar');
    }

    public function testInvalidSpecificationCallback()
    {
        $this->setExpectedException(InvalidCallbackResultException::class);

        $this->createSubImport(function () {
            // Intentionally empty.
        });

        $this->import();
    }

    private function createSubImport($specification = null)
    {
        $this->subImport = $subImport = new SubImport($specification ?: MockFactory::mockImportSpecification());

        $subImport->setPorter(
            $this->porter = \Mockery::mock(Porter::class)
                ->shouldReceive('import')
                ->andReturn(new \EmptyIterator)
                ->byDefault()
                ->getMock()
        );
    }

    private function import($data = null, $context = null)
    {
        return call_user_func($this->subImport, $data, $context);
    }
}
