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
use ScriptFUSION\Porter\Specification\Specification;
use ScriptFUSIONTest\MockFactory;

/**
 * Tests the throttle hierarchy of precedence.
 *
 * Specification throttle (preferred) > Connector throttle (default).
 *
 * @see ImportConnectorFactory
 */
final class ThrottlePrecedenceHierarchyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private Throttle|MockInterface $specificationThrottle;

    private Throttle|MockInterface $connectorThrottle;

    private Specification $specification;

    private Provider|MockInterface $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specificationThrottle = MockFactory::mockThrottle();
        $this->connectorThrottle = MockFactory::mockThrottle();

        $this->specification = new Specification(MockFactory::mockResource(
            $this->provider = MockFactory::mockProvider()
        ));
    }

    /**
     * Tests that when the connector is non-throttling, the specification's throttle is used.
     */
    public function testNonThrottledConnector(): void
    {
        $this->specification->setThrottle($this->specificationThrottle);
        $this->specificationThrottle->expects('async')->once()->andReturn(Future::complete());
        $this->connectorThrottle->expects('async')->never();

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->provider->getConnector(),
            $this->specification
        );

        $connector->fetch(\Mockery::mock(DataSource::class));
    }

    /**
     * Tests that when the connector is throttled, and the specification's throttle is not set, the connector's
     * throttle is used.
     */
    public function testThrottledConnector(): void
    {
        $this->specificationThrottle->expects('async')->never();
        $this->connectorThrottle->expects('async')->once()->andReturn(Future::complete());

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->mockThrottledConnector(),
            $this->specification
        );

        $connector->fetch(\Mockery::mock(DataSource::class));
    }

    /**
     * Tests that when both the connector is throttled and the specification's throttle are set, the specification's
     * throttle overrides that of the connector.
     */
    public function testThrottledConnectorOverride(): void
    {
        $this->specification->setThrottle($this->specificationThrottle);
        $this->specificationThrottle->expects('async')->once()->andReturn(Future::complete());
        $this->connectorThrottle->expects('async')->never();

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->mockThrottledConnector(),
            $this->specification
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
