<?php
namespace ScriptFUSION\Porter;

trait PorterAwareTrait
{
    private $porter;

    /**
     * @param Porter $porter
     *
     * @return $this
     */
    public function setPorter(Porter $porter)
    {
        $this->porter = $porter;

        return $this;
    }
}
