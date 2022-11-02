<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Specification;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Specification\StaticDataSpecification;

/**
 * @see StaticDataSpecification
 */
final class StaticDataImportSpecificationTest extends TestCase
{
    public function test(): void
    {
        $records = (new Porter(\Mockery::spy(ContainerInterface::class)))
            ->import(new StaticDataSpecification(new \ArrayIterator([$output = ['foo']])));

        self::assertSame($output, $records->current());
    }
}
