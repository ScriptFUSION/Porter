<?php
namespace ScriptFUSIONTest\Unit\Porter\Provider;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderDataType;

final class ProviderTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var Provider */
    private $provider;

    private $connector;

    protected function setUp()
    {
        $this->provider = \Mockery::mock(
            Provider::class,
            [$this->connector = \Mockery::mock(Connector::class)]
        )->makePartial();
    }

    public function testFetch()
    {
        self::assertSame(
            'foo',
            $this->provider->fetch(
                \Mockery::mock(ProviderDataType::class)
                    ->shouldReceive('fetch')
                    ->with($this->connector)
                    ->andReturn('foo')
                    ->getMock()
                    ->shouldReceive('getProviderName')
                    ->andReturn(get_class($this->provider))
                    ->getMock()
            )
        );
    }
}
