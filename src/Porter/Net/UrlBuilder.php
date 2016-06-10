<?php
namespace ScriptFUSION\Porter\Net;

use ScriptFUSION\Porter\Connector\Http\HttpOptions;
use Zend\Uri\UriFactory;

class UrlBuilder
{
    private $options;

    public function __construct(HttpOptions $options)
    {
        $this->options = $options;
    }

    public function buildUrl($endpoint, $params = [])
    {
        $uri = UriFactory::factory($endpoint);

        if (!$uri->isAbsolute() && $this->options->getBaseUrl()) {
            // Convert relative URL to absolute URL using base URL.
            $uri->resolve($this->options->getBaseUrl());
        }

        // Merge query parameters.
        $uri->setQuery(
            array_merge(
                $this->options->getQueryParameters(),
                array_merge($uri->getQueryAsArray(), $params)
            )
        );

        return "$uri";
    }
}
