<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Connector\AsyncConnector;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\AsyncProvider;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSIONTest\MockFactory;

abstract class PorterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected Porter $porter;
    protected Provider|AsyncProvider|MockInterface $provider;
    protected ProviderResource|AsyncResource|MockInterface $resource;
    protected ProviderResource|AsyncResource|MockInterface $singleResource;
    protected Connector|AsyncConnector|MockInterface $connector;
    protected ImportSpecification|AsyncImportSpecification $specification;
    protected ImportSpecification|AsyncImportSpecification $singleSpecification;
    protected ContainerInterface|MockInterface $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->porter = new Porter($this->container = \Mockery::spy(ContainerInterface::class));

        $this->registerProvider($this->provider = MockFactory::mockProvider());
        $this->connector = $this->provider->getConnector();
        $this->resource = MockFactory::mockResource($this->provider);
        $this->specification = new ImportSpecification($this->resource);
        $this->singleResource = MockFactory::mockSingleRecordResource($this->provider);
        $this->singleSpecification = new ImportSpecification($this->singleResource);
    }

    protected function registerProvider(Provider|AsyncProvider $provider, string $name = null): void
    {
        $name = $name ?? \get_class($provider);

        $this->container
            ->shouldReceive('has')->with($name)->andReturn(true)
            ->shouldReceive('get')->with($name)->andReturn($provider)->byDefault()
        ;
    }

    /**
     * Arranges for the current connector to throw an exception in the retry callback.
     */
    protected function arrangeConnectorException(\Exception $exception): void
    {
        $this->connector->shouldReceive('fetch')->andThrow($exception);
    }
}
