<?php
namespace ScriptFUSION\Porter\Options;

/**
 * Encapsulates a collection of implementation-defined name and value pairs.
 */
abstract class EncapsulatedOptions
{
    private $options = [];

    /**
     * Creates a copy of the encapsulated options.
     *
     * @return array Options.
     */
    final public function copy()
    {
        return $this->options;
    }

    /**
     * Gets the value for the specified option name. Returns the specified
     * default value if option name is not set.
     *
     * @param string $option Option name.
     * @param mixed $default Default value.
     *
     * @return mixed Option value or default value.
     */
    final protected function get($option, $default = null)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return $default;
    }

    /**
     * Gets a pointer to the specified option name.
     *
     * @param string $option Option name.
     *
     * @return mixed
     */
    final protected function &getReference($option)
    {
        return $this->options[$option];
    }

    /**
     * Sets the specified option name to the specified value.
     *
     * @param string $option Option name.
     * @param mixed $value Value.
     *
     * @return $this
     */
    final protected function set($option, $value)
    {
        $this->options[$option] = $value;

        return $this;
    }
}
