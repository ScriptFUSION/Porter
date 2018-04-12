<?php
namespace ScriptFUSIONTest\Integration\Porter\Specification;

use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;

/**
 * @see StaticDataImportSpecification
 */
final class StaticDataImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    public function test(): void
    {
        $records = (new Porter(\Mockery::spy(ContainerInterface::class)))
            ->import(new StaticDataImportSpecification(new \ArrayIterator([$output = ['foo']])));

        self::assertSame($output, $records->current());
    }
}
