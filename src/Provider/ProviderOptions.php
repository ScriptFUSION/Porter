<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Provides a method for accessing provider options.
 */
interface ProviderOptions
{
    /**
     * Gets the provider options.
     *
     * @return EncapsulatedOptions
     */
    public function getOptions();
}
