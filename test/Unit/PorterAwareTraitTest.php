<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit;

use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\PorterAwareTrait;

final class PorterAwareTraitTest extends TestCase
{
    public function testGetSetPorter(): void
    {
        /** @var PorterAwareTrait $porterAware */
        $porterAware = $this->getObjectForTrait(PorterAwareTrait::class);

        $porterAware->setPorter($porter = new Porter(\Mockery::mock(ContainerInterface::class)));

        self::assertSame(
            $porter,
            \Closure::bind(
                fn () => $this->getPorter(),
                $porterAware,
                $porterAware
            )()
        );
    }
}
