<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAwareTrait;

final class PorterAwareTraitTest extends \PHPUnit_Framework_TestCase
{
    public function testGetSetPorter()
    {
        /** @var PorterAwareTrait $porterAware */
        $porterAware = $this->getObjectForTrait(PorterAwareTrait::class);

        $porterAware->setPorter($porter = \Mockery::mock(Porter::class));

        self::assertSame(
            $porter,
            call_user_func(
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
