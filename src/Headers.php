<?php
namespace Poirot\Mail\Address;

use Poirot\Mail\FactoryHeader;
use Poirot\Mail\Interfaces\iHeader;
use Poirot\Mail\Interfaces\iHeaders;
use Poirot\Std\Struct\CollectionObject;


class Headers
    implements iHeaders
    , \IteratorAggregate # implement \Traversable
{
    /** @var CollectionObject */
    protected $ObjectCollection;


    /**
     * Construct
     *
     * $headers:
     *   ['Header-Label' => 'value, values', ..]
     *   [iHeader, ..]
     *
     * @param array $headers
     */
    function __construct(array $headers = array())
    {
        foreach ($headers as $label => $h) {
            if (! $h instanceof iHeader )
                // Header-Label: value header
                $h = FactoryHeader::of( array($label => $h) );

            $this->insert($h);
        }
    }

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
    function insert($header)
    {
        if (! $header instanceof iHeader )
            throw new \InvalidArgumentException(sprintf(
                'Header must instance of iHeader; given: (%s).'
                , \Poirot\Std\flatten($header)
            ));


        $this->getIterator()->insert($header, array('label'=> strtolower($header->getLabel())));
        return $this;
    }

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
    function get($label)
    {
        $r = $this->getIterator()->find( array('label' => strtolower($label)) );
        return $r;
    }

    /**
     * Has Header With Specific Label?
     *
     * ! headers label are case-insensitive
     *
     * @param string $label
     *
     * @return bool
     */
    function has($label)
    {
        $r = $this->getIterator()->find( array('label' => strtolower($label)) );
        foreach ($r as $v)
            return true;

        return false;
    }

    /**
     * Delete a Header With Label Name
     *
     * @param string $label
     *
     * @return $this
     */
    function del($label)
    {
        if (! $this->has($label) )
            return $this;

        // ..

        $headers = $this->getIterator()->find( array('label' => strtolower($label)) );
        foreach ($headers as $hash => $header)
            $this->getIterator()->del($hash);

        return $this;
    }

    /**
     * Remove All Entities Item
     *
     * @return $this
     */
    function clean()
    {
        $this->getIterator()->clean();
    }


    // Implement Traversable

    /**
     * @return CollectionObject
     */
    function getIterator()
    {
        if (! $this->ObjectCollection )
            $this->ObjectCollection = new CollectionObject;

        return $this->ObjectCollection;
    }

    /**
     * Count elements of an object
     * @return int The custom count as an integer.
     */
    function count()
    {
        return $this->getIterator()->count();
    }


    // ..

    function __clone()
    {
        if ($this->ObjectCollection)
            $this->ObjectCollection = null;
    }
}
