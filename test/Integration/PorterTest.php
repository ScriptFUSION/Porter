<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration;

use Amp\PHPUnit\AsyncTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
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

abstract class PorterTest extends AsyncTestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Porter
     */
    protected $porter;

    /**
     * @var Provider|AsyncProvider|MockInterface
     */
    protected $provider;

    /**
     * @var ProviderResource|AsyncResource|MockInterface
     */
    protected $resource;

    /**
     * @var ProviderResource|AsyncResource|MockInterface
     */
    protected $singleResource;

    /**
     * @var Connector|AsyncConnector|MockInterface
     */
    protected $connector;

    /**
     * @var ImportSpecification|AsyncImportSpecification
     */
    protected $specification;

    /**
     * @var ImportSpecification|AsyncImportSpecification
     */
    protected $singleSpecification;

    /**
     * @var ContainerInterface|MockInterface
     */
    protected $container;

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

    /**
     * @param Provider|AsyncProvider $provider
     * @param string|null $name
     */
    protected function registerProvider($provider, string $name = null): void
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
