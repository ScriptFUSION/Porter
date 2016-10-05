<?php
namespace ScriptFUSION\Porter\Net\Http;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;
use ScriptFUSION\Porter\Type\StringType;

/**
 * TODO. Add missing HTTP context option accessors and mutators.
 */
final class HttpOptions extends EncapsulatedOptions
{
    public function __construct()
    {
        $this->setDefaults([
            'queryParameters' => [],
            'header' => [],
        ]);
    }

    /**
     * @return array
     */
    public function getQueryParameters()
    {
        return $this->get('queryParameters');
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
     * @return string
     */
    public function getMethod()
    {
        return $this->get('method');
    }

    /**
     * @param string $method
     *
     * @return $this
     */
    public function setMethod($method)
    {
        return $this->set('method', "$method");
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->get('header');
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
     * @return string
     */
    public function getContent()
    {
        return $this->get('content');
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        return $this->set('content', "$content");
    }

    /**
     * @return string
     */
    public function getProxy()
    {
        return $this->get('proxy');
    }

    /**
     * @param string $proxy
     *
     * @return $this
     */
    public function setProxy($proxy)
    {
        return $this->set('proxy', "$proxy");
    }

    /**
     * Extracts a list of HTTP context options only.
     *
     * @return array HTTP context options.
     *
     * @see http://php.net/manual/en/context.http.php
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
