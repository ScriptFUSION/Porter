<?php
namespace ScriptFUSION\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Net\UrlBuilder;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Fetches data from an HTTP server via the PHP wrapper.
 *
 * Enhanced error reporting is achieved by ignoring HTTP error codes in the wrapper, instead throwing
 * HttpServerException which includes the body of the response in the error message.
 */
class HttpConnector extends CachingConnector
{
    /** @var HttpOptions */
    private $options;

    /** @var UrlBuilder */
    private $urlBuilder;

    /** @var string */
    private $baseUrl;

    public function __construct(HttpOptions $options = null)
    {
        parent::__construct();

        $this->options = $options ?: new HttpOptions;
    }

    /**
     * {@inheritdoc}
     *
     * @param string $source Source.
     * @param EncapsulatedOptions|null $options Optional. Options.
     *
     * @return string Response.
     *
     * @throws \InvalidArgumentException Options is not an instance of HttpOptions.
     * @throws HttpConnectionException Failed to connect to source.
     * @throws HttpServerException Server sent an error code.
     */
    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        if ($options && !$options instanceof HttpOptions) {
            throw new \InvalidArgumentException('Options must be an instance of HttpOptions.');
        }

        if (false === $response = @file_get_contents(
            $this->getOrCreateUrlBuilder()->buildUrl(
                $source,
                $options ? $options->getQueryParameters() : [],
                $this->getBaseUrl()
            ),
            false,
            stream_context_create([
                'http' => ['ignore_errors' => true] + array_merge(
                    $this->options->extractHttpContextOptions(),
                    $options ? $options->extractHttpContextOptions() : []
                ),
            ])
        )) {
            $error = error_get_last();
            throw new HttpConnectionException($error['message'], $error['type']);
        }

        $code = explode(' ', $http_response_header[0], 3)[1];
        if ($code < 200 || $code >= 400) {
            throw new HttpServerException(
                "HTTP server responded with error: \"$http_response_header[0]\".\n\n$response"
            );
        }

        return $response;
    }

    private function getOrCreateUrlBuilder()
    {
        return $this->urlBuilder ?: $this->urlBuilder = new UrlBuilder($this->options);
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = "$baseUrl";

        return $this;
    }
}
