<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use ScriptFUSION\Porter\Connector\ConnectionContext;
use ScriptFUSION\Porter\Connector\Connector;
use ScriptFUSION\Porter\Porter;
use ScriptFUSION\Porter\Provider\Provider;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSIONTest\MockFactory;

abstract class PorterTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var Porter
     */
    protected $porter;

    /**
     * @var Provider|MockInterface
     */
    protected $provider;

    /**
     * @var ProviderResource|MockInterface
     */
    protected $resource;

    /**
     * @var Connector|MockInterface
     */
    protected $connector;

    /**
     * @var ImportSpecification
     */
    protected $specification;

    /**
     * @var ContainerInterface|MockInterface
     */
    protected $container;

    protected function setUp()
    {
        $this->porter = new Porter($this->container = \Mockery::spy(ContainerInterface::class));

        $this->registerProvider($this->provider = MockFactory::mockProvider());
        $this->connector = $this->provider->getConnector();
        $this->resource = MockFactory::mockResource($this->provider);
        $this->specification = new ImportSpecification($this->resource);
    }

    protected function registerProvider(Provider $provider, string $name = null): void
    {
        $name = $name ?: \get_class($provider);

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
        $this->connector->shouldReceive('fetch')->with(
            \Mockery::on(function (ConnectionContext $context) use ($exception) {
                $context->retry(function () use ($exception) {
                    throw $exception;
                });
            }),
            \Mockery::any()
        );
    }
}
