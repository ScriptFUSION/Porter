<?php
namespace ScriptFUSION\Porter\Net\Soap;

use ScriptFUSION\Porter\Options\EncapsulatedOptions;

/**
 * Encapsulates SOAP context options.
 */
final class SoapOptions extends EncapsulatedOptions
{
    public function __construct()
    {
        $this->setDefaults([
            'params' => [],
        ]);
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->get('params');
    }

    /**
     * @param array $params
     *
     * @return $this
     */
    public function setParameters(array $params)
    {
        return $this->set('params', $params);
    }

    /**
     * @return string
     */
    public function getLocation()
    {
        return $this->get('location');
    }

    /**
     * @param string $location
     *
     * @return $this
     */
    public function setLocation($location)
    {
        return $this->set('location', "$location");
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return $this->get('uri');
    }

    /**
     * @param string $uri
     *
     * @return $this
     */
    public function setUri($uri)
    {
        return $this->set('uri', "$uri");
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->get('soap_version');
    }

    /**
     * @param int $version
     *
     * @return $this
     */
    public function setVersion($version)
    {
        return $this->set('soap_version', $version|0);
    }

    /**
     * @return bool
     */
    public function getCompression()
    {
        return $this->get('compression');
    }

    /**
     * @param bool $compression
     *
     * @return $this
     */
    public function setCompression($compression)
    {
        return $this->set('compression', $compression|0);
    }

    /**
     * @return bool
     */
    public function getKeepAlive()
    {
        return $this->get('keep_alive');
    }

    /**
     * @param bool $keepAlive
     *
     * @return $this
     */
    public function setKeepAlive($keepAlive)
    {
        return $this->set('keep_alive', (bool)$keepAlive);
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setProxyHost($host)
    {
        return $this->set('proxy_host', "$host");
    }

    /**
     * @return string
     */
    public function getProxyHost()
    {
        return $this->get('proxy_host');
    }

    /**
     * @return int
     */
    public function getProxyPort()
    {
        return $this->get('proxy_port');
    }

    /**
     * @param int $port
     *
     * @return $this
     */
    public function setProxyPort($port)
    {
        return $this->set('proxy_port', $port|0);
    }

    /**
     * @return string
     */
    public function getProxyLogin()
    {
        return $this->get('proxy_login');
    }

    /**
     * @param string $login
     *
     * @return $this
     */
    public function setProxyLogin($login)
    {
        return $this->set('proxy_login', "$login");
    }

    /**
     * @return string
     */
    public function getProxyPassword()
    {
        return $this->get('proxy_password');
    }

    /**
     * @param string $password
     *
     * @return $this
     */
    public function setProxyPassword($password)
    {
        return $this->set('proxy_password', "$password");
    }

    /**
     * @return string
     */
    public function getEncoding()
    {
        return $this->get('encoding');
    }

    /**
     * Sets the character encoding for strings.
     *
     * @param string $encoding Character encoding, e.g. utf-8.
     *
     * @return $this
     */
    public function setEncoding($encoding)
    {
        return $this->set('encoding', "$encoding");
    }

    /**
     * @return bool
     */
    public function getTrace()
    {
        return $this->get('trace');
    }

    /**
     * @param bool $trace
     *
     * @return $this
     */
    public function setTrace($trace)
    {
        return $this->set('trace', (bool)$trace);
    }

    /**
     * @return bool
     */
    public function getExceptions()
    {
        return $this->get('exceptions');
    }

    /**
     * @param bool $exceptions
     *
     * @return $this
     */
    public function setExceptions($exceptions)
    {
        return $this->set('exceptions', (bool)$exceptions);
    }

    /**
     * @return array
     */
    public function getClassmap()
    {
        return $this->get('classmap');
    }

    /**
     * @param array $classmap
     *
     * @return $this
     */
    public function setClassmap(array $classmap)
    {
        return $this->set('classmap', $classmap);
    }

    /**
     * @return int
     */
    public function getConnectionTimeout()
    {
        return $this->get('connection_timeout');
    }

    /**
     * @param int $timeout
     *
     * @return $this
     */
    public function setConnectionTimeout($timeout)
    {
        return $this->set('connection_timeout', $timeout|0);
    }

    /**
     * @return array
     */
    public function getTypemap()
    {
        return $this->get('typemap');
    }

    /**
     * @param array $typemap
     *
     * @return $this
     */
    public function setTypemap(array $typemap)
    {
        return $this->set('typemap', $typemap);
    }

    /**
     * @return int
     */
    public function getCacheWsdl()
    {
        return $this->get('cache_wsdl');
    }

    /**
     * Sets the WSDL caching mode.
     *
     * @param int $cacheMode One of WSDL_CACHE_NONE, WSDL_CACHE_DISK, WSDL_CACHE_MEMORY or WSDL_CACHE_BOTH.
     *
     * @return $this
     */
    public function setCacheWsdl($cacheMode)
    {
        return $this->set('cache_wsdl', $cacheMode|0);
    }

    /**
     * @return string
     */
    public function getUserAgent()
    {
        return $this->get('user_agent');
    }

    /**
     * @param string $userAgent
     *
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        return $this->set('user_agent', "$userAgent");
    }

    /**
     * @return int
     */
    public function getFeatures()
    {
        return $this->get('features');
    }

    /**
     * @param int $features A bitmask one of SOAP_SINGLE_ELEMENT_ARRAYS, SOAP_USE_XSI_ARRAY_TYPE,
     * SOAP_WAIT_ONE_WAY_CALLS.
     *
     * @return $this
     */
    public function setFeatures($features)
    {
        return $this->set('features', $features|0);
    }

    /**
     * @return int
     */
    public function getSslMethod()
    {
        return $this->get('ssl_method');
    }

    /**
     * @param int $method Ssl method to use of SOAP_SSL_METHOD_TLS, SOAP_SSL_METHOD_SSLv2, SOAP_SSL_METHOD_SSLv3 or
     * SOAP_SSL_METHOD_SSLv23.
     *
     * @return $this
     */
    public function setSslMethod($method)
    {
        return $this->set('ssl_method', $method|0);
    }

    /**
     * Extracts a list of SOAP Client options only.
     *
     * @return array SOAP context options.
     *
     * @see http://php.net/manual/en/soapclient.soapclient.php
     */
    public function extractSoapClientOptions()
    {
        return array_intersect_key(
            $this->copy(),
            array_flip([
                'location',
                'uri',
                'style',
                'use',
                'soap_version',
                'proxy_host',
                'proxy_port',
                'proxy_login',
                'proxy_password',
                'local_cert',
                'passphrase',
                'authentication',
                'compression',
                'encoding',
                'trace',
                'classmap',
                'exceptions',
                'connection_timeout',
                'typemap',
                'cache_wsdl',
                'user_agent',
                'stream_context',
                'features',
                'keep_alive',
                'ssl_method',
            ])
        );
    }
}
