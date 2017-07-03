<?php
namespace Poirot\Mail\Header;


class GenericHeader
    extends aHeader
{
    /**
     *
     * @param string $label
     * @param null|array|\Traversable $data
     */
    function __construct($label, $data = null)
    {
        $this->setLabel($label);

        parent::__construct($data);
    }
}
