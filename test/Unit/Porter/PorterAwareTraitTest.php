<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Porter;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAwareTrait;

final class PorterAwareTraitTest extends TestCase
{
    public function testGetSetPorter(): void
    {
        /** @var PorterAwareTrait $porterAware */
        $porterAware = $this->getObjectForTrait(PorterAwareTrait::class);

        $porterAware->setPorter($porter = \Mockery::mock(Porter::class));

        self::assertSame(
            $porter,
            \Closure::bind(
                function (): Porter {
                    /** @var PorterAwareTrait $this */
                    return $this->getPorter();
                },
                $porterAware,
                $porterAware
            )()
        );
    }
}
