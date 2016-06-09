<?php
namespace ScriptFUSIONTest\Integration\Porter\Connector;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSIONTest\Porter\Options\TestOptions;

final class CachingConnectorTest extends \PHPUnit_Framework_TestCase
{
    use MockeryPHPUnitIntegration;

    /** @var CachingConnector|MockInterface $connector */
    private $connector;

    /** @var TestOptions */
    private $options;

    protected function setUp()
    {
        $this->connector = \Mockery::mock(CachingConnector::class, [])->makePartial()
            ->shouldReceive('fetchFreshData')
            ->andReturn('foo', 'bar')
            ->getMock();

        $this->options = (new TestOptions)->setFoo('foo');
    }

    public function testCacheEnabled()
    {
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
    }

    public function testCacheDisabled()
    {
        $this->connector->disableCache();

        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
        self::assertSame('bar', $this->connector->fetch('baz', $this->options));
    }

    public function testCacheBypassedForDifferentOptions()
    {
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));

        $this->options->setFoo('bar');

        self::assertSame('bar', $this->connector->fetch('baz', $this->options));
    }

    public function testCacheUsedForDifferentOptionsInstance()
    {
        self::assertSame('foo', $this->connector->fetch('baz', $this->options));
        self::assertSame('foo', $this->connector->fetch('baz', clone $this->options));
    }

    public function testNullAndEmptyAreEquivalent()
    {
        $options = new TestOptions;
        self::assertEmpty($options->copy());
        self::assertSame('foo', $this->connector->fetch('baz', $options));

        self::assertSame('foo', $this->connector->fetch('baz'));
    }
}
