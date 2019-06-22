<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit\Porter;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\Connector\Recoverable\ExponentialAsyncDelayRecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\AsyncResource;
use ScriptFUSION\Porter\Specification\AsyncImportSpecification;
use ScriptFUSION\Porter\Specification\IncompatibleTransformerException;
use ScriptFUSION\Porter\Transform\AsyncTransformer;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * @see AsyncImportSpecification
 */
final class AsyncImportSpecificationTest extends TestCase
{
    /** @var AsyncImportSpecification */
    private $specification;

    protected function setUp(): void
    {
        $this->specification = new AsyncImportSpecification(\Mockery::mock(AsyncResource::class));
    }

    /**
     * Tests that only async transformers can be added.
     */
    public function testAddTransformer(): void
    {
        $this->specification->addTransformer($transformer = \Mockery::mock(AsyncTransformer::class));
        self::assertContains($transformer, $this->specification->getTransformers());

        $this->expectException(IncompatibleTransformerException::class);
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
}
