<?php
namespace ScriptFUSION\Porter\Provider;

final class ProviderFactory
{
    public function createProvider($name)
    {
        switch ($name) {
            case StaticDataProvider::class:
                return new StaticDataProvider;
        }

        throw new ObjectNotCreatedException("Factory does not know how to create: \"$name\".");
    }
}
