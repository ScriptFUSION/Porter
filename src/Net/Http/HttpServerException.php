<?php
namespace ScriptFUSION\Porter\Net\Http;

use ScriptFUSION\Porter\Connector\RecoverableConnectorException;

/**
 * The exception that is thrown when the server responds with an error code.
 */
class HttpServerException extends RecoverableConnectorException
{
    /**
     * @var string Response body.
     */
    private $body;

    /**
     * Initializes this instance with the specified HTTP error message, HTTP response code and response body.
     *
     * @param string $message HTTP error message.
     * @param int $code HTP response code.
     * @param string $body Response body.
     */
    public function __construct($message, $code, $body)
    {
        parent::__construct($message, $code);

        $this->body = "$body";
    }

    /**
     * Gets the response body.
     *
     * @return string Response body.
     */
    public function getBody()
    {
        return $this->body;
    }
}
