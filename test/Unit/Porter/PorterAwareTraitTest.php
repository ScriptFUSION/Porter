<?php
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
            \call_user_func(
                \Closure::bind(
                    function () {
                        /** @var PorterAwareTrait $this */
                        return $this->getPorter();
                    },
                    $porterAware,
                    $porterAware
                )
            )
        );
    }
}
