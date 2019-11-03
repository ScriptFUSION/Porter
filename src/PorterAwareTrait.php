<?php
declare(strict_types=1);

namespace ScriptFUSION\Porter;

trait PorterAwareTrait
{
    /** @var Porter */
    private $porter;

    private function getPorter(): Porter
    {
        return $this->porter;
    }

    public function setPorter(Porter $porter): self
    {
        $this->porter = $porter;

        return $this;
    }
}
