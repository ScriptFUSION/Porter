<?php
namespace ScriptFUSION\Porter\Net\Http;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Type\StringType;

/**
 * TODO. Add missing HTTP context option accessors and mutators.
 */
final class HttpOptions extends EncapsulatedOptions
{
    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->get('baseUrl');
    }

    /**
     * @param string $baseUrl
     *
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        return $this->set('baseUrl', "$baseUrl");
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->get('queryParameters', []);
    }

    /**
     * @param array $queryParameters
     *
     * @return $this
     */
    public function setQueryParameters(array $queryParameters)
    {
        return $this->set('queryParameters', $queryParameters);
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->get('header', []);
    }

    /**
     * @param string $header
     *
     * @return $this
     */
    public function addHeader($header)
    {
        $this->getReference('header')[] = "$header";

        return $this;
    }

    public function removeHeaders($name)
    {
        foreach ($this->findHeaders($name) as $key => $_) {
            unset($this->getReference('header')[$key]);
        }

        return $this;
    }

    public function replaceHeaders($name, $header)
    {
        return $this->removeHeaders($name)->addHeader($header);
    }

    /**
     * Find the first header matching the specified name.
     *
     * @param string $name Header name.
     *
     * @return string|null Header if found, otherwise null.
     */
    public function findHeader($name)
    {
        if ($headers = $this->findHeaders($name)) {
            return reset($headers);
        }
    }

    /**
     * Find all headers matching the specified name.
     *
     * @param string $name Header name.
     *
     * @return array Headers.
     */
    public function findHeaders($name)
    {
        return array_filter($this->getHeaders(), function ($header) use ($name) {
            return StringType::startsWith($header, "$name:");
        });
    }

    /**
     * Extracts a list of HTTP context options only.
     *
     * @return array HTTP context options.
     */
    public function extractHttpContextOptions()
    {
        return array_intersect_key(
            $this->copy(),
            array_flip([
                'method',
                'header',
                'user_agent',
                'content',
                'proxy',
                'request_fulluri',
                'follow_location',
                'max_redirects',
                'protocol_version',
                'timeout',
                'ignore_errors',
            ])
        );
    }
}
