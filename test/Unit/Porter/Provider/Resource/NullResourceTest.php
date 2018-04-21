<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Porter\Provider\Resource;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\Resource\NullResource;
use ScriptFUSIONTest\FixtureFactory;

final class NullResourceTest extends TestCase
{
    public function test(): void
    {
        self::assertFalse(
            (new NullResource)->fetch(
                FixtureFactory::buildImportConnector(\Mockery::mock(Connector::class))
            )->valid()
        );
    }
}
