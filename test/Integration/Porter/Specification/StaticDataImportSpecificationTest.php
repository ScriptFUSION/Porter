<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter\Specification;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\StaticDataImportSpecification;

/**
 * @see StaticDataImportSpecification
 */
final class StaticDataImportSpecificationTest extends TestCase
{
    public function test(): void
    {
        $records = (new Porter(\Mockery::spy(ContainerInterface::class)))
            ->import(new StaticDataImportSpecification(new \ArrayIterator([$output = ['foo']])));

        self::assertSame($output, $records->current());
    }
}
