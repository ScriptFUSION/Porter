<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter\Options;

/**
 * Encapsulates a collection of implementation-defined name and value pairs.
 */
abstract class EncapsulatedOptions
{
    private $options = [];

    private $defaults = [];

    /**
     * Creates a copy of the encapsulated options.
     *
     * @return array Options.
     */
    final public function copy(): array
    {
        return $this->options + $this->defaults;
    }

    /**
     * Gets the value for the specified option name. When option name is not set the default value is retrieved, if
     * defined, otherwise null.
     *
     * @param string $option Option name.
     *
     * @return mixed Option value, default value or null.
     */
    final protected function get(string $option)
    {
        if (array_key_exists($option, $this->options)) {
            return $this->options[$option];
        }

        if (array_key_exists($option, $this->defaults)) {
            return $this->defaults[$option];
        }
    }

    /**
     * Gets a pointer to the specified option name.
     *
     * @param string $option Option name.
     *
     * @return mixed
     */
    final protected function &getReference(string $option)
    {
        return $this->options[$option];
    }

    /**
     * Sets the specified option name to the specified value.
     *
     * @param string $option Option name.
     * @param mixed $value Value.
     */
    final protected function set(string $option, $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets the default values for the specified map of keys and values.
     *
     * @param array $defaults Map of keys and default values.
     */
    final protected function setDefaults(array $defaults): self
    {
        $this->defaults = $defaults;

        return $this;
    }
}
