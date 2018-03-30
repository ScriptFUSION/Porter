<?php
namespace ScriptFUSION\Porter\Type;

use ScriptFUSION\StaticClass;

/**
 * Provides methods for working with PHP objects.
 */
final class ObjectType
{
    use StaticClass;

    /**
     * Converts the specified object to an array, recursively converting any
     * nested arrays of objects.
     *
     * @param mixed $mixed Object.
     *
     * @return array Converted object.
     */
    public static function toArray($mixed)
    {
        if (is_object($mixed)) {
            $mixed = get_object_vars($mixed);
        }

        if (is_array($mixed)) {
            return array_map(__METHOD__, $mixed);
        }

        return $mixed;
    }
}
