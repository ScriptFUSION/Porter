<?php
namespace ScriptFUSION\Porter\Provider;

interface ProviderDataType
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * @return ProviderName
     */
    public function getName();
}
