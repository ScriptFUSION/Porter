<?php
namespace ScriptFUSIONTest\Unit\Porter;

use ScriptFUSION\Porter\Cache\CacheAdvice;
use ScriptFUSION\Porter\Provider\Resource\ProviderResource;
use ScriptFUSION\Porter\Specification\DuplicateTransformerException;
use ScriptFUSION\Porter\Specification\ImportSpecification;
use ScriptFUSION\Porter\Transform\Transformer;
use ScriptFUSIONTest\Stubs\Invokable;

final class ImportSpecificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImportSpecification */
    private $specification;

    /** @var ProviderResource */
    private $resource;

    protected function setUp()
    {
        $this->specification = new ImportSpecification(
            $this->resource = \Mockery::mock(ProviderResource::class)
        );
    }

    public function testClone()
    {
        $this->specification
            ->addTransformer(\Mockery::mock(Transformer::class))
            ->setContext($context = (object)[])
            ->setFetchExceptionHandler($handler = new Invokable)
        ;

        $specification = clone $this->specification;

        self::assertNotSame($this->resource, $specification->getResource());

        self::assertNotSame(
            array_values($this->specification->getTransformers()),
            array_values($specification->getTransformers())
        );
        self::assertNotSame(
            array_keys($this->specification->getTransformers()),
            array_keys($specification->getTransformers())
        );
        self::assertCount(count($this->specification->getTransformers()), $specification->getTransformers());

        self::assertNotSame($context, $specification->getContext());
        self::assertNotSame($handler, $specification->getFetchExceptionHandler());
    }

    public function testProviderData()
    {
        self::assertSame($this->resource, $this->specification->getResource());
    }

    public function testAddTransformer()
    {
        self::assertEmpty($this->specification->getTransformers());

        $this->specification->addTransformer($transformer1 = \Mockery::mock(Transformer::class));
        self::assertCount(1, $this->specification->getTransformers());
        self::assertContains($transformer1, $this->specification->getTransformers());

        $this->specification->addTransformer($transformer2 = \Mockery::mock(Transformer::class));
        self::assertCount(2, $this->specification->getTransformers());
        self::assertContains($transformer1, $this->specification->getTransformers());
        self::assertContains($transformer2, $this->specification->getTransformers());
    }

    public function testAddTransformers()
    {
        self::assertEmpty($this->specification->getTransformers());

        $this->specification->addTransformers([
            $transformer1 = \Mockery::mock(Transformer::class),
            $transformer2 = \Mockery::mock(Transformer::class),
        ]);

        self::assertCount(2, $this->specification->getTransformers());
        self::assertContains($transformer1, $this->specification->getTransformers());
        self::assertContains($transformer2, $this->specification->getTransformers());
    }

    public function testAddSameTransformer()
    {
        $this->specification->addTransformer($transformer = \Mockery::mock(Transformer::class));

        $this->setExpectedException(DuplicateTransformerException::class);
        $this->specification->addTransformer($transformer);
    }

    public function testContext()
    {
        self::assertSame('foo', $this->specification->setContext('foo')->getContext());
    }

    public function testCacheAdvice()
    {
        self::assertSame(
            $advice = CacheAdvice::MUST_CACHE(),
            $this->specification->setCacheAdvice($advice)->getCacheAdvice()
        );
    }

    /**
     * @param mixed $input
     * @param int $output
     *
     * @dataProvider provideFetchAttempts
     */
    public function testMaxFetchAttempts($input, $output)
    {
        self::assertSame($output, $this->specification->setMaxFetchAttempts($input)->getMaxFetchAttempts());
    }

    public function provideFetchAttempts()
    {
        return [
            // Valid.
            [1, 1],
            [2, 2],

            // Invalid.
            'Too low, positive' => [0, 1],
            'Too low, negative' => [-1, 1],
            'Float in range' => [1.9, 1],
        ];
    }

    public function testExceptionHandler()
    {
        self::assertSame(
            $handler = new Invokable,
            $this->specification->setFetchExceptionHandler($handler)->getFetchExceptionHandler()
        );
    }
}
