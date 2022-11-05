<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Import;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Import\StaticImport;

/**
 * @see StaticImport
 */
final class StaticImportTest extends TestCase
{
    public function test(): void
    {
        $records = (new Porter(\Mockery::spy(ContainerInterface::class)))
            ->import(new StaticImport(new \ArrayIterator([$output = ['foo']])));

        self::assertSame($output, $records->current());
    }
}
