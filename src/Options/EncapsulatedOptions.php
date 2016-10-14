<?php
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
    final public function copy()
    {
        return $this->options + $this->defaults;
    }

    /**
     * Merges the specified overriding options into this instance giving precedence to the overriding options.
     *
     * @param EncapsulatedOptions $overridingOptions Overriding options.
     */
    final public function merge(EncapsulatedOptions $overridingOptions)
    {
        if (!$overridingOptions instanceof static) {
            throw new MergeException('Cannot merge: options must be an instance of "' . get_class($this) . '".');
        }

        $this->options = $this->mergeOptions($overridingOptions, $overridingOptions->options, $this->options);
    }

    /**
     * Merges the specified overriding options with the options of this instance giving precedence to the overriding
     * options.
     *
     * Overriding this method in a derived class allows it to implement a custom merging strategy using the overriding
     * options or the specified explicit overriding options and the specified explicit options of this instance.
     * Explicit options are those set explicitly and exclude any default values.
     *
     * @param EncapsulatedOptions $overridingOptions Overriding options.
     * @param array $explicitOverridingOptions Overriding options excluding defaults.
     * @param array $explicitOptions This instance's options excluding defaults.
     *
     * @return array Merged options.
     */
    protected function mergeOptions(
        EncapsulatedOptions $overridingOptions,
        array $explicitOverridingOptions,
        array $explicitOptions
    ) {
        return $overridingOptions->copy() + $this->copy();
    }

    /**
     * Gets the value for the specified option name. Returns the specified
     * default value if option name is not set.
     *
     * @param string $option Option name.
     *
     * @return mixed Option value or default value.
     */
    final protected function get($option)
    {
        if (array_key_exists($key = "$option", $this->options)) {
            return $this->options[$key];
        }

        if (array_key_exists($key, $this->defaults)) {
            return $this->defaults[$key];
        }
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
        return $this->options["$option"];
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
        $this->options["$option"] = $value;

        return $this;
    }

    /**
     * Sets the default values for the specified map of keys and values.
     *
     * @param array $defaults Map of keys and default values.
     *
     * @return $this
     */
    final protected function setDefaults(array $defaults)
    {
        $this->defaults = $defaults;

        return $this;
    }
}
