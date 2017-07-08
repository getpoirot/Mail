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
        // TODO Implement this
    }

    /**
     * Print Email Address
     *
     * @param iMailAddress $address
     *
     * @return string
     */
    function sprintAddress(iMailAddress $address, $encode = false)
    {
        $name  = $address->getName();
        $email = $address->getEmail();

        if (! empty($name) ) {
            $name = sprintf('"%s"', $name);

            (false == $encode) ?: $name = \Poirot\Mail\Header\encodeHeaderValue($name, null, 998);
        }


        if ( $encode && preg_match('/^(.+)@([^@]+)$/', $email, $matches) )
        {
            $localPart = $matches[1];
            $hostname  = $matches[2];

            if (extension_loaded('intl'))
                $hostname = (idn_to_ascii($hostname) ?: $hostname);

            $email = sprintf('%s@%s', $localPart, $hostname);
        }


        if ($name === '' || $name === null )
            return $email;

        return sprintf('%s <%s>', $name, $email);
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
    use Poirot\Http\Interfaces\iHeader;
    use Poirot\Mail\Interfaces\iMailMessage;


    /**
     * Is Message Valid?
     *
     * @param iMailMessage $message
     *
     * @return bool
     */
    function isValid(iMailMessage $message)
    {
        # we must have from address as header
        #
        $flag = $message->headers()->has('from');


        return $flag;
    }

    /**
     * // TODO encode header values
     *
     * Print Mail Message as String
     *
     * @param iMailMessage $message
     *
     * @return string
     */
    function sprintMessage(iMailMessage $message, $encode = false)
    {
        # Render Headers
        #
        /** @var iHeader $hs */
        $rendered = []; $headers = '';
        foreach ($message->headers() as $hs)
        {
            $label = $hs->getLabel();
            if ( isset($rendered[strtolower($label)]) )
                // this value is render and combined!
                continue;

            /** @var iHeader $h */
            foreach ($message->headers()->get($label) as $h)
                $value[] = $h->renderValueLine();

            // glue them back
            $value    = implode(',' . "\r\n", $value);
            $headers .= $label.': '. $value;

            $rendered[$label] = true;
        }

        $headers.="\r\n";


        # Render Message Body
        #
        return $headers
            . $message->getBody();
    }

    /**
     * Parse Mail Message From Raw String
     *
     * @param $messageStr
     *
     * @return string
     */
    function parseMessageFromString($messageStr)
    {
        // TODO implement this
    }

    /**
     * Create Message-ID
     *
     * @return string
     */
    function createMessageId()
    {
        $time = time();

        if ( isset($_SERVER['REMOTE_ADDR']) )
            $user = $_SERVER['REMOTE_ADDR'];
        else
            $user = getmypid();


        $rand = mt_rand();

        if ( isset($_SERVER["SERVER_NAME"]) )
            $hostName = $_SERVER["SERVER_NAME"];
        else
            $hostName = php_uname('n');

        return sha1($time . $user . $rand) . '@' . $hostName;
    }
}


namespace Poirot\Mail\Header
{
    // Most codes here are Clone of Poirot\Http\Header\$
    use Poirot\Std\Type\StdString;


    /**
     * // TODO headers with same name?!!
     * Parse Headers
     *
     * @param string $content String content include headers
     * @param null   $offset  Read until headers end (double return afterward) and set
     *                        offset position here
     *
     * @return array [ [Content-Type] =>  application/javascript, .. ]
     * @throws \Exception
     */
    function parseHeadersFromMessage($content, &$offset = null, $EOL = "\r\n")
    {
        $content = (string) $content;

        $heads = []; $offset = 0; $headStarting = false;
        while ( preg_match("/.*[$EOL]?/", $content, $matchLines, null, $offset) )
        {
            $line = $matchLines[0];
            $offset += strlen($line);

            $line = trim($line, $EOL);

            if ( empty($line) ) {
                // When headers start parse headers until one break line reached
                if (! $headStarting )
                    continue; // lines not started; trim begining empty lines

                break;
            }

            if (preg_match('/^[\x21-\x39\x3B-\x7E]+:.*$/', $line))
            {
                // Start Header
                $headStarting = true;
                list($label, $value) = splitLabelValue($line);
                $heads[$label] = $value;

                continue; // try next line

            } elseif (! $headStarting )
                throw new \RuntimeException(sprintf(
                    '(%s) is malformed in headers.'
                    , $line
                ));


            // continuation: append to current line
            // recover the whitespace that break the line (unfolding, rfc2822#section-2.2.3)
            if (preg_match('/^\s+.*$/', $line))
                $heads[$label] .= ' ' . trim($line);
            else
                // Line does not match header format!
                throw new \RuntimeException(sprintf(
                    'Line "%s" does not match header format!',
                    $line
                ));
        }


        if ( empty($heads) )
            throw new \InvalidArgumentException('Error Parsing Request Message.');

        return $heads;
    }

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
     * // TODO mb_encode_mimeheader
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
