<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Unit;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Async\Throttle\Throttle;
use ScriptFUSION\Porter\Connector\Recoverable\RecoverableExceptionHandler;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Import\DuplicateTransformerException;
use ScriptFUSION\Porter\Import\Import;
use ScriptFUSION\Porter\Transform\Transformer;

/**
 * @see Import
 */
final class ImportTest extends TestCase
{
    private Import $import;

    private ProviderResource $resource;

    protected function setUp(): void
    {
        $this->import = new Import(
            $this->resource = \Mockery::mock(ProviderResource::class)
        );
    }

    public function testClone(): void
    {
        $this->import
            ->addTransformer(\Mockery::mock(Transformer::class))
            ->setContext($context = (object)[])
            ->setRecoverableExceptionHandler($handler = \Mockery::mock(RecoverableExceptionHandler::class))
        ;

        $import = clone $this->import;

        self::assertNotSame($this->resource, $import->getResource());

        self::assertNotSame(
            array_values($this->import->getTransformers()),
            array_values($import->getTransformers())
        );
        self::assertNotSame(
            array_keys($this->import->getTransformers()),
            array_keys($import->getTransformers())
        );
        self::assertCount(\count($this->import->getTransformers()), $import->getTransformers());

        self::assertNotSame($context, $import->getContext());
        self::assertNotSame($handler, $import->getRecoverableExceptionHandler());
    }

    public function testGetResource(): void
    {
        self::assertSame($this->resource, $this->import->getResource());
    }

    public function testProviderName(): void
    {
        self::assertNull($this->import->getProviderName());
        self::assertSame($name = 'foo', $this->import->setProviderName($name)->getProviderName());
        self::assertNull($this->import->setProviderName(null)->getProviderName());
    }

    public function testAddTransformer(): void
    {
        self::assertEmpty($this->import->getTransformers());

        $this->import->addTransformer($transformer1 = \Mockery::mock(Transformer::class));
        self::assertCount(1, $this->import->getTransformers());
        self::assertContains($transformer1, $this->import->getTransformers());

        $this->import->addTransformer($transformer2 = \Mockery::mock(Transformer::class));
        self::assertCount(2, $this->import->getTransformers());
        self::assertContains($transformer1, $this->import->getTransformers());
        self::assertContains($transformer2, $this->import->getTransformers());

        $this->expectException(\TypeError::class);
        $this->import->addTransformer(\Mockery::mock(AsyncTransformer::class));
    }

    public function testAddTransformers(): void
    {
        self::assertEmpty($this->import->getTransformers());

        $this->import->addTransformers([
            $transformer1 = \Mockery::mock(Transformer::class),
            $transformer2 = \Mockery::mock(Transformer::class),
        ]);

        self::assertCount(2, $this->import->getTransformers());
        self::assertContains($transformer1, $this->import->getTransformers());
        self::assertContains($transformer2, $this->import->getTransformers());
    }

    public function testAddSameTransformer(): void
    {
        $this->import->addTransformer($transformer = \Mockery::mock(Transformer::class));

        $this->expectException(DuplicateTransformerException::class);
        $this->import->addTransformer($transformer);
    }

    public function testClearTransformers(): void
    {
        $this->import->addTransformer($transformer = \Mockery::mock(Transformer::class));
        self::assertContains($transformer, $this->import->getTransformers());

        $this->import->clearTransformers();
        self::assertEmpty($this->import->getTransformers());
    }

    public function testContext(): void
    {
        self::assertSame($context = 'foo', $this->import->setContext($context)->getContext());
    }

    public function testCache(): void
    {
        self::assertFalse($this->import->mustCache());

        $this->import->enableCache();
        self::assertTrue($this->import->mustCache());

        $this->import->disableCache();
        self::assertFalse($this->import->mustCache());
    }

    /**
     * @dataProvider provideValidFetchAttempts
     */
    public function testValidMaxFetchAttempts(int $value): void
    {
        self::assertSame($value, $this->import->setMaxFetchAttempts($value)->getMaxFetchAttempts());
    }

    public function provideValidFetchAttempts(): array
    {
        return [
            [1],
            [PHP_INT_MAX],
        ];
    }

    /**
     * @dataProvider provideInvalidFetchAttempts
     */
    public function testInvalidMaxFetchAttempts(int|float $value, string $exceptionType): void
    {
        $this->expectException($exceptionType);
        $this->import->setMaxFetchAttempts($value);
    }

    public function provideInvalidFetchAttempts(): array
    {
        return [
            'Too low, positive' => [0, \InvalidArgumentException::class],
            'Too low, negative' => [-1, \InvalidArgumentException::class],
            'Float in range' => [1.9, \TypeError::class],
        ];
    }

    public function testExceptionHandler(): void
    {
        self::assertSame(
            $handler = \Mockery::mock(RecoverableExceptionHandler::class),
            $this->import->setRecoverableExceptionHandler($handler)->getRecoverableExceptionHandler()
        );
    }

    /**
     * Tests that a custom throttle can be set.
     */
    public function testThrottle(): void
    {
        self::assertSame(
            $throttle = \Mockery::mock(Throttle::class),
            $this->import->setThrottle($throttle)->getThrottle()
        );
    }
}
