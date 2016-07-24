<?php
namespace ScriptFUSION\Porter\Net;

use ScriptFUSION\Porter\Net\Http\HttpOptions;
use Zend\Uri\UriFactory;

class UrlBuilder
{
    private $options;

    public function __construct(HttpOptions $options)
    {
        $this->options = $options;
    }

    public function buildUrl($endpoint, array $params = [], $baseUrl = null)
    {
        $uri = UriFactory::factory($endpoint);

        if ($baseUrl && !$uri->isAbsolute()) {
            // Convert relative URL to absolute URL using base URL.
            $uri->resolve($baseUrl);
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
