<?php
namespace Poirot\Mail\Interfaces;

use Poirot\Std\Interfaces\Struct\iCollection;


interface iHeaders
    extends iCollection
{
    /**
     * Set Header
     *
     * ! headers label are case-insensitive
     *
     * @param iHeader $header
     *
     * @return $this
     * @throws \InvalidArgumentException
     */
    function insert($header);

    /**
     * Get Header With Label
     *
     * ! headers label are case-insensitive
     *
     * @param string $label Header Label
     *
     * @return \Generator|\Traversable[iHeader]
     * @throws \Exception header not found
     */
    function get($label);

    /**
     * Delete a Header With Label Name
     *
     * @param string $label
     *
     * @return $this
     */
    function del($label);

    /**
     * Has Header With Specific Label?
     *
     * ! headers label are case-insensitive
     *
     * @param string $label
     *
     * @return bool
     */
    function has($label);
}
