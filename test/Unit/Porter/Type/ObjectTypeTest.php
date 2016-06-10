<?php
namespace ScriptFUSIONTest\Unit\Porter\Type;

use ScriptFUSION\Porter\Type\ObjectType;

final class ObjectTypeTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $object = json_decode(json_encode(
            $array = [
                'foo' => [
                    'bar',
                ],
            ]
        ));

        self::assertSame($array, ObjectType::toArray($object));
    }
}
