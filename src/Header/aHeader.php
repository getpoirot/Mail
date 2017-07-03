<?php
namespace Poirot\Mail\Header;

use Poirot\Mail\Interfaces\iHeader;
use Poirot\Std\Struct\DataOptionsOpen;


class aHeader
    extends DataOptionsOpen
    implements iHeader
{
    const CRLF = "\r\n";

    protected $label;
    protected $encoding;


    /**
     * Set Header Label
     *
     * @param string $label
     *
     * @return $this
     */
    function setLabel($label)
    {
        // normalize header label
        $label = str_replace(' ', '-', ucwords(str_replace(['_', '-'], ' ', (string) $label)));

        if (! isValidLabel($label) )
            throw new \InvalidArgumentException(sprintf(
                'Invalid header name "%s".'
                , is_null($label) ? 'null' : $label
            ));


        $this->label = $label;
        return $this;
    }

    /**
     * Get Header Label
     * @ignore
     *
     * @return string
     */
    function getLabel()
    {
        return $this->label;
    }

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
    function render($callable = null)
    {
        $label = $this->getLabel();
        if ( empty($label) )
            throw new \Exception('Header label is empty.');

        $value = $this->renderValueLine();
        ($callable === null) ?: call_user_func($callable, $value);

        return $label.': '.$value.self::CRLF;
    }

    /**
     * Get Field Value As String
     *
     * @return string
     */
    function renderValueLine()
    {
        return $this->_buildStringRepresentationOfValues($this);
    }


    // ..

    protected function _buildStringRepresentationOfValues($options, $valuePart = false)
    {
        if ($options instanceof \Traversable)
            $options = iterator_to_array($options);

        if (! is_array($options) )
            throw new \InvalidArgumentException(sprintf(
                '(%s) is not knowing header value option.',
                \Poirot\Std\flatten($options)
            ));


        /*
         * [ ['audio/mp3', 'q'=>'0.2', 'version'=>'0.5'], 'audio/basic+mp3' ]
         */
        foreach ($options as $k => $v)
        {
            // Accept: audio/mp3; q=0.2; version=0.5, audio/basic+mp3
            if ( $valuePart && is_array($v) )
                // [ ['audio/mp3', 'version'=> ['not_allowed', 'this'] ], 'audio/basic+mp3' ]
                throw new \Exception('Array For Value Parts.');


            if ( is_array($v) ) {
                // ['audio/mp3', 'q'=>'0.2', 'version'=>'0.5']
                $v = $this->_buildStringRepresentationOfValues($v, true);
                $options[$k] = $v;
            }

            if ( is_string($k) )
                // q=0.2; version=0.5
                $options[$k] = "$k=$v";
        }

        $separator = ($valuePart) ? '; ' : ', ';
        return implode($separator, $options);
    }
}
