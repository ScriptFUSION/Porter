<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Provider\Resource;

use ScriptFUSION\Porter\IncompatibleResourceException;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Provider\Resource\SingleRecordResource;

/**
 * @see IncompatibleResourceException
 */
final class IncompatibleResourceExceptionTest extends TestCase
{
    public function testCreateMustImplementInterface(): void
    {
        self::assertMatchesRegularExpression(
            sprintf('[.%s\\.]', addslashes(SingleRecordResource::class)),
            IncompatibleResourceException::createMustImplementInterface()->getMessage()
        );
    }
}
