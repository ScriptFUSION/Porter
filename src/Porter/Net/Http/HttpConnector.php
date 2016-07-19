<?php
namespace ScriptFUSION\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\CachingConnector;
use ScriptFUSION\Porter\Net\UrlBuilder;
use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Retry\ErrorHandler\ExponentialBackoffErrorHandler;

class HttpConnector extends CachingConnector
{
    /** @var HttpOptions */
    private $options;

    /** @var UrlBuilder */
    private $urlBuilder;

    public function __construct(HttpOptions $options = null)
    {
        parent::__construct();

        $this->options = $options ?: new HttpOptions;
    }

    public function fetchFreshData($source, EncapsulatedOptions $options = null)
    {
        if ($options && !$options instanceof HttpOptions) {
            throw new \InvalidArgumentException('Options must be an instance of HttpOptions.');
        }

        return \ScriptFUSION\Retry\retry(5, function () use ($source, $options) {
            if (false === $response = @file_get_contents(
                $this->getOrCreateUrlBuilder()->buildUrl($source, $options ? $options->getQueryParameters() : []),
                false,
                stream_context_create([
                    'http' => array_merge(
                        $this->options->extractHttpContextOptions(),
                        $options ? $options->extractHttpContextOptions() : []
                    ),
                ])
            )) {
                throw new HttpConnectionException(error_get_last()['message']);
            }

            return $response;
        }, new ExponentialBackoffErrorHandler);
    }

    private function getOrCreateUrlBuilder()
    {
        return $this->urlBuilder ?: $this->urlBuilder = new UrlBuilder($this->options);
    }
}
