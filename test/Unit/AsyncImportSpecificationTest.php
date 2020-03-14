<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Recoverable\ExponentialAsyncDelayRecoverableExceptionHandler;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Transform\AsyncTransformer;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * @see AsyncImportSpecification
 */
final class AsyncImportSpecificationTest extends TestCase
{
    /** @var AsyncImportSpecification */
    private $specification;

    /** @var AsyncResource */
    private $resource;

    protected function setUp(): void
    {
        $this->specification = new AsyncImportSpecification($this->resource = \Mockery::mock(AsyncResource::class));
    }

    /**
     * Tests that only async transformers can be added.
     */
    public function testAddTransformer(): void
    {
        $this->specification->addTransformer($transformer = \Mockery::mock(AsyncTransformer::class));
        self::assertContains($transformer, $this->specification->getTransformers());

        $this->expectException(\TypeError::class);
        $this->specification->addTransformer(\Mockery::mock(Transformer::class));
    }

    /**
     * Tests that the default exception handler is of the expected type.
     */
    public function testDefaultExceptionHandler(): void
    {
        self::assertInstanceOf(
            ExponentialAsyncDelayRecoverableExceptionHandler::class,
            $this->specification->getRecoverableExceptionHandler()
        );
    }

    public function testClone(): void
    {
        $this->specification
            ->addTransformer(\Mockery::mock(AsyncTransformer::class))
            ->setContext($context = new class {
                // Intentionally empty.
            })
            ->setRecoverableExceptionHandler($handler = \Mockery::mock(RecoverableExceptionHandler::class))
        ;

        $specification = clone $this->specification;

        self::assertNotSame($this->resource, $specification->getAsyncResource());

        self::assertNotSame(
            array_values($this->specification->getTransformers()),
            array_values($specification->getTransformers())
        );
        self::assertNotSame(
            array_keys($this->specification->getTransformers()),
            array_keys($specification->getTransformers())
        );
        self::assertCount(\count($this->specification->getTransformers()), $specification->getTransformers());

        self::assertNotSame($context, $specification->getContext());
        self::assertNotSame($handler, $specification->getRecoverableExceptionHandler());
    }

    /**
     * Tests that a custom throttle can be set.
     */
    public function testThrottle(): void
    {
        self::assertSame(
            $throttle = \Mockery::mock(Throttle::class),
            $this->specification->setThrottle($throttle)->getThrottle()
        );
    }

    /**
     * Tests that when no throttle is set, a default throttle is produced.
     */
    public function testDefaultThrottle(): void
    {
        self::assertInstanceOf(Throttle::class, $this->specification->getThrottle());
    }
}
