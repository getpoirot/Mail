<?php
namespace Poirot\Mail\Interfaces;

use Poirot\Std\Interfaces\Struct\iDataOptions;


interface iHeader
    extends iDataOptions
{
    /**
     * Set Header Label
     *
     * @param string $label
     *
     * @return $this
     */
    function setLabel($label);

    /**
     * Get Header Label
     * @ignored not consider as data options
     *
     * @return string
     */
    function getLabel();

    /**
     * Get Field Value As String
     *
     * @return string
     */
    function renderValueLine();

    /**
     * Represent Header As String
     *
     * - filter values just before output
     *
     * from rfc:
     * Header fields are lines composed of a field name, followed by a colon
     * (":"), followed by a field body, and terminated by CRLF.
     *
     * @param callable $callable Filter value line
     *        function($value) : string
     *
     * @return string
     * @throws \Exception
     */
    function render($callable = null);
}
