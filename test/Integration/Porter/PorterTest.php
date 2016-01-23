<?php
namespace ScriptFUSIONTest\Integration\Porter;

use Mockery\MockInterface;
use ScriptFUSION\Porter\Collection\ProviderRecords;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\ProviderData;
use ScriptFUSION\Porter\ProviderNotFoundException;
use ScriptFUSION\Porter\Specification\ImportSpecification;

final class PorterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Porter */
    private $porter;

    /** @var Provider|MockInterface */
    private $provider;

    /** @var ProviderData */
    private $providerData;

    protected function setUp()
    {
        $this->porter = (new Porter)->addProvider($this->provider = \Mockery::mock(Provider::class));
        $this->providerData = \Mockery::mock(ProviderData::class)
            ->shouldReceive('getProviderName')
            ->andReturn(get_class($this->provider))
            ->getMock();
    }

    public function testGetProvider()
    {
        $this->assertSame($this->provider, $this->porter->getProvider(get_class($this->provider)));
    }

    public function testGetInvalidProvider()
    {
        $this->setExpectedException(ProviderNotFoundException::class);

        (new Porter)->getProvider('foo');
    }

    public function testAddProviders()
    {
        $this->porter->addProviders([
            $this->provider,
            $provider = $this->getMockBuilder(Provider::class)->disableOriginalConstructor()->getMock(),
        ]);

        $this->assertSame($this->provider, $this->porter->getProvider(get_class($this->provider)));
        $this->assertSame($provider, $this->porter->getProvider(get_class($provider)));
    }

    public function testImport()
    {
        $this->provider->shouldReceive('fetch')->andReturn(new \ArrayIterator(['foo']));

        $records = $this->porter->import(new ImportSpecification($this->providerData));

        $this->assertInstanceOf(ProviderRecords::class, $records);
        $this->assertSame('foo', $records->current());
    }

    public function testFilter()
    {
        $this->provider->shouldReceive('fetch')->andReturn(new \ArrayIterator(range(1, 10)));

        $records = $this->porter->import(
            (new ImportSpecification($this->providerData))
                ->setFilter(function ($record) {
                    return $record % 2;
                })
        );

        $this->assertSame([1, 3, 5, 7, 9], iterator_to_array($records));
    }
}
