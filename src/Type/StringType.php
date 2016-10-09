<?php
namespace ScriptFUSION\Porter\Type;

use ScriptFUSION\StaticClass;

final class StringType
{
    use StaticClass;

    public static function startsWith($string, $start)
    {
        return strpos($string, $start) === 0;
    }

    public static function endsWith($string, $end)
    {
        // Needle cannot be found if needle is longer than haystack.
        if (($offset = strlen($string) - strlen($end)) >= 0) {
            return strpos($string, $end, $offset) === $offset;
        }

        return false;
    }
}
