<?php
declare(strict_types=1);

namespace ScriptFUSIONTest\Integration\Porter;

use PHPUnit\Framework\TestCase;
use ScriptFUSION\Porter\ExceptionDescriptor;

final class ExceptionDescriptorTest extends TestCase
{
    /**
     * @dataProvider provideMatches
     */
    public function testMatches(ExceptionDescriptor $descriptor, \Exception $exception): void
    {
        self::assertTrue($descriptor->matches($exception));
    }

    public function provideMatches(): array
    {
        return [
            'Class match' => [new ExceptionDescriptor(\LogicException::class), new \LogicException],
            'Subclass match' => [new ExceptionDescriptor(\LogicException::class), new \DomainException],
            'Message exact match' => [
                (new ExceptionDescriptor(\Exception::class))
                    ->setMessage('foo'),
                new \Exception('foo')
            ],
            'Null message' => [
                (new ExceptionDescriptor(\Exception::class))
                    ->setMessage(null),
                new \Exception('foo')
            ],
        ];
    }

    /**
     * @dataProvider provideNonMatches
     */
    public function testNonMatches(ExceptionDescriptor $descriptor, \Exception $exception): void
    {
        self::assertFalse($descriptor->matches($exception));
    }

    public function provideNonMatches(): array
    {
        return [
            'Class mismatch' => [new ExceptionDescriptor(\LogicException::class), new \RuntimeException],
            'Superclass mismatch' => [new ExceptionDescriptor(\DomainException::class), new \LogicException],
            'Message mismatch' => [
                (new ExceptionDescriptor(\Exception::class))
                    ->setMessage('foo'),
                new \Exception('bar')
            ],
            'Message blank' => [
                (new ExceptionDescriptor(\Exception::class))
                    ->setMessage(''),
                new \Exception('foo')
            ],
            'Message partial match' => [
                (new ExceptionDescriptor(\Exception::class))
                    ->setMessage('foo'),
                new \Exception('foo ')
            ],
            'Message case mismatch' => [
                (new ExceptionDescriptor(\Exception::class))
                    ->setMessage('foo'),
                new \Exception('Foo')
            ]
        ];
    }
}
