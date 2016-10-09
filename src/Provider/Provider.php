<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Provider\Resource\ProviderResource;

/**
 * Provides a method for fetching data from a resource.
 */
interface Provider
{
    /**
     * Fetches data from the specified resource.
     *
     * @param ProviderResource $resource Resource.
     *
     * @return \Iterator Enumerable data series.
     */
    public function fetch(ProviderResource $resource);
}
