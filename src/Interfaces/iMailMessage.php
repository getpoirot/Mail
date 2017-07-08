<?php
namespace Poirot\Mail\Interfaces;

use Poirot\Stream\Interfaces\iStreamable;


interface iMailMessage
{
    /**
     * Set Mail Headers
     *
     * @param iHeaders $headers
     *
     * @return $this
     */
    function setHeaders(iHeaders $headers);

    /**
     * Headers
     *
     * @return iHeaders
     */
    function headers();

    /**
     * Set Message Body Content
     *
     * @param string|iStreamable $body
     *
     * @return $this
     */
    function setBody($body);

    /**
     * Message Body
     *
     * @return string|iStreamable|null
     */
    function getBody();
}
