<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

/**
 * Defines a method for injecting an instance of Porter into an object.
 */
interface PorterAware
{
    /**
     * Sets an instance of Porter.
     */
    public function setPorter(Porter $porter): self;
}
