<?php
namespace ScriptFUSIONTest\Unit\Porter\Mapper\Strategy;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Mapper\Strategy\SubImport;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\ProviderDataType;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class SubImportTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var MockInterface|SubImport */
    private $import;

    /** @var MockInterface|Porter */
    private $porter;

    private $specification;

    protected function setUp()
    {
        $this->import = $import = new SubImport(
            $this->specification = \Mockery::mock(ImportSpecification::class, [\Mockery::mock(ProviderDataType::class)])
        );

        $import->setPorter(
            $this->porter = \Mockery::mock(Porter::class)
                ->shouldReceive('import')
                ->andReturn(new \EmptyIterator)
                ->byDefault()
                ->getMock()
        );
    }

    public function testImport()
    {
        self::assertNull($this->import());

        $this->porter->shouldReceive('import')->andReturn(new \ArrayIterator($array = range(1, 5)));
        self::assertSame($array, $this->import());
    }

    public function testPreImportCallback()
    {
        $this->import->addSpecificationCallback(
            function ($data, $context, ImportSpecification $specification) {
                self::assertSame('foo', $data);
                self::assertSame('bar', $context);
                self::assertSame($this->specification, $specification);
            }
        );

        $this->import('foo', 'bar');
    }

    private function import($data = null, $context = null)
    {
        $import = $this->import;

        return $import($data, $context);
    }
}
