<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderDataType;

final class ProviderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    public function testConnector()
    {
        /** @var Provider $provider */
        $provider = \Mockery::mock(
            Provider::class,
            [$connector = \Mockery::mock(Connector::class)->makePartial()]
        )->makePartial();

        $this->assertSame($connector, $provider->getConnector());
    }

    public function testFetch()
    {
        $this->setExpectedException(\LogicException::class);

        /** @var Provider $provider */
        $provider = \Mockery::mock(Provider::class)->makePartial();
        $provider->fetch(\Mockery::mock(ProviderDataType::class));
    }
}
