<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Functional;

use Amp\Future;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Connector\DataSource;
use ScriptFUSION\Porter\Connector\ImportConnectorFactory;
use ScriptFUSION\Porter\Connector\ThrottledConnector;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Import\Import;
use ScriptFUSIONTest\MockFactory;

/**
 * Tests the throttle hierarchy of precedence.
 *
 * Import throttle (preferred) > Connector throttle (default).
 *
 * @see ImportConnectorFactory
 */
final class ThrottlePrecedenceHierarchyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Throttle|MockInterface $importThrottle;

    private Throttle|MockInterface $connectorThrottle;

    private Import $import;

    private Provider|MockInterface $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importThrottle = MockFactory::mockThrottle();
        $this->connectorThrottle = MockFactory::mockThrottle();

        $this->import = new Import(MockFactory::mockResource(
            $this->provider = MockFactory::mockProvider()
        ));
    }

    /**
     * Tests that when the connector is non-throttling, the import's throttle is used.
     */
    public function testNonThrottledConnector(): void
    {
        $this->import->setThrottle($this->importThrottle);
        $this->importThrottle->expects('async')->once()->andReturn(Future::complete());
        $this->connectorThrottle->expects('async')->never();

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->provider->getConnector(),
            $this->import
        );

        $connector->fetch(\Mockery::mock(DataSource::class));
    }

    /**
     * Tests that when the connector is throttled, and the import's throttle is not set, the connector's
     * throttle is used.
     */
    public function testThrottledConnector(): void
    {
        $this->importThrottle->expects('async')->never();
        $this->connectorThrottle->expects('async')->once()->andReturn(Future::complete());

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->mockThrottledConnector(),
            $this->import
        );

        $connector->fetch(\Mockery::mock(DataSource::class));
    }

    /**
     * Tests that when both the connector is throttled and the import's throttle are set, the import's
     * throttle overrides that of the connector.
     */
    public function testThrottledConnectorOverride(): void
    {
        $this->import->setThrottle($this->importThrottle);
        $this->importThrottle->expects('async')->once()->andReturn(Future::complete());
        $this->connectorThrottle->expects('async')->never();

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->mockThrottledConnector(),
            $this->import
        );

        $connector->fetch(\Mockery::mock(DataSource::class));
    }

    private function mockThrottledConnector(): Connector|ThrottledConnector
    {
        return \Mockery::mock(Connector::class, ThrottledConnector::class)
            ->shouldReceive('getThrottle')
                ->andReturn($this->connectorThrottle)
            ->getMock()
        ;
    }
}
