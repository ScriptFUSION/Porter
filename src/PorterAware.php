<?php
namespace ScriptFUSION\Porter;

/**
 * Defines a method for injecting an instance of Porter into an object.
 */
interface PorterAware
{
    /**
     * Sets an instance of Porter.
     *
     * @param Porter $porter Porter.
     *
     * @return $this
     */
    public function setPorter(Porter $porter);
}
