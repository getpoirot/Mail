<?php
namespace Poirot\Mail\Address
{
    use Poirot\Mail\MailAddress;
    use Poirot\Mail\Interfaces\iMailAddress;


    /**
     * Parse Address From SingleLine String
     *
     * @param $str
     *
     * @return MailAddress|iMailAddress
     */
    function parseAddressFromString($str)
    {

    }

    /**
     * Print Email Address
     *
     * @param iMailAddress $address
     *
     * @return string
     */
    function sprintAddress(iMailAddress $address)
    {
        $name = $address->getName();

        if ($name === '' || $name === null )
            return $this->getEmail();


        // TODO Encode Header

        return sprintf('%s <%s>', $name, $address->getEmail());
    }

    /**
     * Glue Address List Together
     *
     * @param array $addressList
     *
     * @return string
     */
    function glueAddresses(array $addressList)
    {
        return implode(
            ",\r\n ",
            array_map(
                function (MailAddress $addressAndName) {
                    return (string) $addressAndName;
                },
                $addressList
            )
        );
    }
}


namespace Poirot\Mail\Message
{
    use Poirot\Mail\Interfaces\iMailMessage;

    /**
     * Is Message Valid?
     *
     * - we must have from address as header
     *
     * @param iMailMessage $message
     */
    function isValid(iMailMessage $message)
    {
        // TODO Implement this
    }

    /**
     * Print Mail Message as String
     *
     * @param iMailMessage $message
     *
     * @return string
     */
    function sprintMessage(iMailMessage $message)
    {
        // TODO Implement this
    }
}


namespace Poirot\Mail\Header
{
    // Most codes here are Clone of Poirot\Http\Header\$
    use Poirot\Std\Type\StdString;

    /**
     * Parse Header line
     *
     * - name MUST be composed of printable US-ASCII characters (i.e.,
     *   characters that have values between 33 and 126, inclusive),
     *   except colon.
     *
     * @param string $line
     *
     * @return array['label' => 'value_line']
     */
    function splitLabelValue($line)
    {
        if (! preg_match('/^(?P<label>[^()><@,;:\"\\/\[\]?=}{ \t]+):(?P<value>.*)$/', $line, $matches))
            if ($matches === false)
                throw new \InvalidArgumentException(sprintf(
                    'Invalid Header (%s).'
                    , $line
                ));

        return array( $matches['label'] => trim($matches['value'], "\r\n") );
    }

    /**
     * Fun To Use While Render Header as Argument
     *
     * @return \Closure
     */
    function funEncodeHeaderValue()
    {
        return function ($value) {
            return encodeHeaderValue($value);
        };
    }

    /**
     * Encode String into standard header value word wrap
     *
     * @param string      $value
     * @param null|string $encoding 'ASCII'|'UTF-8'|....
     * @param int         $lineLength
     *
     * @return string
     */
    function encodeHeaderValue($value, $encoding = null, $lineLength = 78)
    {
        $encoding = ($encoding !== null) ? $encoding : getStrEncoding($value);

        switch ($encoding) {
            case 'UTF-8':
                $value = quoted_printable_encode($value);
                // Mail-Header required chars have to be encoded also:
                $value = str_replace(['?', ' ', '_'], ['=3F', '=20', '=5F'], $value);

                $prefix = sprintf('=?%s?Q?', $encoding);
                $lineLength = $lineLength - strlen($prefix) - 3;

                $lines = _splitTextIntoLines($value, $lineLength);

                // assemble the lines together by pre- and appending delimiters, charset, encoding.
                for ($i = 0, $count = count($lines); $i < $count; $i++) {
                    $lines[$i] = ' ' . $prefix . $lines[$i] . '?=';
                }

                $value = trim(implode("\r\n", $lines));

                break;

            default: $value = wordwrap($value, $lineLength, "\r\n");
        }

        return $value;
    }

    function _splitTextIntoLines($str, $lineLength)
    {

        // initialize first line, we need it anyways
        $lines = [0 => ''];

        // Split encoded text into separate lines
        $tmp = '';
        while (strlen($str) > 0)
        {
            $currentLine = max(count($lines) - 1, 0);

            if (substr($str, 0, 1) === "=")
                $token = substr($str, 0, 3);
            else
                $token = substr($str, 0, 1);

            $substr      = substr($str, strlen($token));
            $str         = (false === $substr) ? '' : $substr;

            $tmp .= $token;
            if ($token === '=20') {
                // only if we have a single char token or space, we can append the
                // tempstring it to the current line or start a new line if necessary.
                $lineLimitReached = (strlen($lines[$currentLine] . $tmp) > $lineLength);
                $noCurrentLine = ($lines[$currentLine] === '');
                if ($noCurrentLine && $lineLimitReached) {
                    $lines[$currentLine] = $tmp;
                    $lines[$currentLine + 1] = '';
                } elseif ($lineLimitReached) {
                    $lines[$currentLine + 1] = $tmp;
                } else {
                    $lines[$currentLine] .= $tmp;
                }
                $tmp = '';
            }

            // don't forget to append the rest to the last line
            if (strlen($str) === 0)
                $lines[$currentLine] .= $tmp;
        }

        return $lines;
    }

    /**
     * Determine The Encoding Type From Value String
     *
     * @param string $val
     *
     * @return string
     */
    function getStrEncoding($val)
    {
        return StdString::of($val)->isPrintable() ? 'ASCII' : 'UTF-8';
    }

    /**
     * Encode Mime
     *
     * @param $value
     * @param $encoding
     * @param int $lineLength
     *
     * @return string
     */
    function encodeMimeValue($value, $encoding, $lineLength = 998)
    {
        // TODO
    }

    /**
     * Decodes a MIME header field
     *
     * convert "Subject: =?UTF-8?B?UHLDvGZ1bmcgUHLDvGZ1bmc=?="
     * to      "Subject: Prüfung Prüfung"
     *
     * @param string $val
     *
     * @return string
     */
    function decodeMimeValue($val)
    {
        $val = iconv_mime_decode( (string) $val, ICONV_MIME_DECODE_CONTINUE_ON_ERROR, 'UTF-8');
        return $val;
    }

    /**
     * Check Is Valid Header Name (label) given?
     *
     * @param string $label
     *
     * @return bool
     */
    function isValidLabel($label)
    {
        return ( preg_match('/^[a-zA-Z0-9\'`#$%&*+.^_|~!-]+$/', $label) );
    }
}
