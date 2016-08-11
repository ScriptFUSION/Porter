<?php
namespace ScriptFUSION\Porter\Provider;

use ScriptFUSION\Porter\Provider\Resource\Resource;

/**
 * Provides a method for fetching data from a resource.
 */
interface Provider
{
    /**
     * Fetches data from the specified resource.
     *
     * @param Resource $resource Resource.
     *
     * @return \Iterator Enumerable data series.
     */
    public function fetch(Resource $resource);
}
