<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Provider;

/**
 * @internal Do not use.
 */
final class ProviderFactory
{
    public function createProvider(string $name): Provider
    {
        switch ($name) {
            case StaticDataProvider::class:
                return new StaticDataProvider;
        }

        throw new ObjectNotCreatedException("Factory does not know how to create: \"$name\".");
    }
}
