<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider\Resource;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Provider\Resource\AbstractResource;

final class AbstractResourceTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    public function testTag()
    {
        /** @var AbstractResource $abstractResource */
        $abstractResource = \Mockery::mock(AbstractResource::class)->makePartial();

        self::assertSame($tag = 'foo', $abstractResource->setProviderTag($tag)->getProviderTag());
    }
}
