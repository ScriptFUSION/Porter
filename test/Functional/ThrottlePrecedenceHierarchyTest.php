<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Functional;

use Amp\PHPUnit\AsyncTestCase;
use Amp\Success;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use ScriptFUSION\Porter\Connector\AsyncConnector;
use ScriptFUSION\Porter\Connector\AsyncDataSource;
use ScriptFUSION\Porter\Connector\ImportConnectorFactory;
use ScriptFUSION\Porter\Connector\ThrottledConnector;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSIONTest\MockFactory;

/**
 * Tests the throttle hierarchy of precedence. Only applies to async imports.
 *
 * Specification throttle (preferred) > Connector throttle (default).
 *
 * @see ImportConnectorFactory
 */
final class ThrottlePrecedenceHierarchyTest extends AsyncTestCase
{
    use MockeryPHPUnitIntegration;

    private $specificationThrottle;

    private $connectorThrottle;

    private $specification;

    private $provider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->specificationThrottle = MockFactory::mockThrottle();
        $this->connectorThrottle = MockFactory::mockThrottle();

        $this->specification = new AsyncImportSpecification(MockFactory::mockResource(
            $this->provider = MockFactory::mockProvider()
        ));
    }

    /**
     * Tests that when the connector is non-throttling, the specification's throttle is used.
     */
    public function testNonThrottledConnector(): \Generator
    {
        $this->specification->setThrottle($this->specificationThrottle);
        $this->specificationThrottle->expects('await')->once()->andReturn(new Success());
        $this->connectorThrottle->expects('await')->never();

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->provider->getAsyncConnector(),
            $this->specification
        );

        yield $connector->fetchAsync(\Mockery::mock(AsyncDataSource::class));
    }

    /**
     * Tests that when the connector is throttled, and the specification's throttle is not set, the connector's
     * throttle is used.
     */
    public function testThrottledConnector(): \Generator
    {
        $this->specificationThrottle->expects('await')->never();
        $this->connectorThrottle->expects('await')->once()->andReturn(new Success());

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->mockThrottledConnector(),
            $this->specification
        );

        yield $connector->fetchAsync(\Mockery::mock(AsyncDataSource::class));
    }

    /**
     * Tests that when both the connector is throttled and the specification's throttle are set, the specification's
     * throttle overrides that of the connector.
     */
    public function testThrottledConnectorOverride(): \Generator
    {
        $this->specification->setThrottle($this->specificationThrottle);
        $this->specificationThrottle->expects('await')->once()->andReturn(new Success());
        $this->connectorThrottle->expects('await')->never();

        $connector = ImportConnectorFactory::create(
            $this->provider,
            $this->mockThrottledConnector(),
            $this->specification
        );

        yield $connector->fetchAsync(\Mockery::mock(AsyncDataSource::class));
    }

    /**
     * @return AsyncConnector|ThrottledConnector
     */
    private function mockThrottledConnector(): AsyncConnector
    {
        return \Mockery::mock(AsyncConnector::class, ThrottledConnector::class)
            ->shouldReceive('getThrottle')
                ->andReturn($this->connectorThrottle)
            ->shouldReceive('fetchAsync')
                ->andReturn(MockFactory::mockPromise())
            ->getMock()
        ;
    }
}
