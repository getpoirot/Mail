<?php
namespace Poirot\Mail\Address;

use Poirot\Mail\Interfaces\iHeaders;
use Poirot\Mail\Interfaces\iMailMessage;
use Poirot\Stream\Interfaces\iStreamable;


class Message
    implements iMailMessage
{
    protected $headers;
    protected $body;


    /**
     * Set Mail Headers
     *
     * @param iHeaders $headers
     *
     * @return $this
     */
    function setHeaders(iHeaders $headers)
    {
        $this->headers = $headers;
        return $this;
    }

    /**
     * Headers
     *
     * @return iHeaders
     */
    function headers()
    {
        if (! $this->headers ) {
            $headers = new Headers;
            $this->headers = $headers;
        }

        return $this->headers;
    }

    /**
     * Set Message Body Content
     *
     * @param string|iStreamable $body
     *
     * @return $this
     */
    function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Message Body
     *
     * @return string|iStreamable|null
     */
    function getBody()
    {
        return $this->body;
    }
}
