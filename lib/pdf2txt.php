<?php

class pdf2txt
{
    var $src;
    var $dest;
    var $data;

    // constructor
    function __construct($_src = false, $_dest = false)
    {
        $this->setSource($_src);
        $this->setDestination($_dest);
    }

    // set data if no conversion from file nescessary
    function setInput($_data)
    {
        $this->data = $_data;
    }

    // sets the source-file
    function setSource($_src)
    {
        $this->src = $_src;
    }

    // sets the destination-file
    function setDestination($_dest)
    {
        $this->dest = $_dest;
    }

    static function directConvert($_data)
    {
        $pdf2txt = new self();
        return $pdf2txt->convert($_data);
    }

    // convert to pdf
    function convert($_data = false)
    {
        if (false !== $_data) {
            $this->data = $_data;
        }

        if ( // load from file?
            (false !== $this->src) AND
            // file exists?
            (false === $this->data = file_get_contents($this->src))
        ) {
            // [ ERROR ]
            // file does not exist
            return false;
        }

        if ($this->data === false) {
            // [ ERROR ]
            // nothing to convert
            return false;
        }


        // ###############################
        // data available -> start parsing
        // ###############################

        // parse encoding
        preg_match('~/Encoding\s*/(\w+)~ism', $this->data, $encoding);

        // detect encoding and assume that there is only a single charset for the hole document
        $fromEncoding = 'windows-1252';
        if (isset($encoding[1])) {
            switch ($encoding[1]) {
                case 'MacRomanEncoding':
                    $fromEncoding = 'macintosh';
                    break;

                case 'WinAnsiEncoding':
                    // standard encoding
                    break;
            }
        }

        // parse data
        // the following code ignores the keyword "stream" and "endstream" if they are in a string
        $isStream = false;
        $stream = '';
        $streams = [];
        $openBracketCount = 0;
        $encodedStream = false;
        foreach (preg_split('~(<<\s*/.*?>>\s*stream\s*)|(\s*endstream\s*)|(\()|(\))~ism', $this->data, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $k => $part) {
            if (preg_match('~<<\s*/(.*?)>>\s*stream\s*~ism', $part, $match)) {
                $switch = 'stream';
                if (false !== strpos($match[1], '/Filter'))
                    $encodedStream = true;
            } else {
                $switch = trim($part);
            }

            switch ($switch) {
                case '(':
                    if ($isStream AND !$encodedStream)
                        $openBracketCount++;
                    break;

                case ')':
                    if ($isStream AND !$encodedStream)
                        $openBracketCount--;
                    break;

                case 'endstream':
                    if ($isStream AND $openBracketCount <= 0) {
                        $isStream = false;
                        $streams[] = $stream;
                        $stream = '';
                        $encodedStream = false;
                    }
                    break;
            }

            if ($isStream) {
                $stream .= $part;
            }

            if ($switch == 'stream') {
                if ($isStream) {
                    $stream .= $part;
                } else {
                    $isStream = true;
                }
            }
        }

        $textObjects = [];
        foreach ($streams as $k => $stream) {
            // uncompress the stream
            if (false === $uncompressed = @gzuncompress($stream))
                // if nothing to uncompress, assume that the stream is already uncompressed
                $uncompressed = $stream;

            // convert to internal encoding UTF-8
            $uncompressed = @iconv($fromEncoding, 'UTF-8', $uncompressed);

            // replace escaped brackets with placeholders
            $text = str_replace(array('\(', '\)', '\[', '\]'), array('##STARTBRACKET##', '##ENDBRACKET##', '##STARTSBRACKET##', '##ENDSBRACKET##'), $uncompressed);

            // parse streams
            // the following code ignores the keyword "BT" and "ET" if they are in a string
            $isTextObj = false;
            $textObject = '';
            $openBracketCount = 0;
            foreach (preg_split('~(\s*BT\s+)|(\s+ET\s+)|(\()|(\))~ism', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $k => $part) {
                $switch = trim($part);
                switch ($switch) {
                    case '(':
                        if ($isTextObj)
                            $openBracketCount++;
                        break;

                    case ')':
                        if ($isTextObj)
                            $openBracketCount--;
                        break;

                    case 'ET':
                        if ($isTextObj AND $openBracketCount <= 0) {
                            $isTextObj = false;
                            $textObjects[] = $textObject;
                            $textObject = '';
                        }
                        break;
                }

                if ($isTextObj) {
                    $textObject .= $part;
                }

                if ($switch == 'BT') {
                    if ($isTextObj) {
                        $textObject .= $part;
                    } else {
                        $isTextObj = true;
                    }
                }
            }
        }

        $return = '';
        $string = '';
        foreach ($textObjects as $textObject) {
            // parse text-objects
            // the following code ignores PDF-keywords if they are in a string
            $isString = false;
            $openBracketCount = 0;

            foreach (preg_split('~(?:\s+(Td|TD|Tm|Tj|Tc|Tf|T\*|"|\')\s+)|(\()|(\))~ism', $textObject, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $k => $part) {
                switch ($part) {
                    // new line
                    case 'Td':
                    case 'TD':
                    case 'T*':
                    case '"':
                    case "'":
                        if (!$isString)
                            $return .= "\n";
                        break;

                    case ')':
                        if ($isString AND $openBracketCount <= 0) {
                            $isString = false;
                            $return .= $string;
                            $string = '';
                        } elseif ($isString)
                            $openBracketCount--;
                        break;
                }

                if ($isString) {
                    $string .= $part;
                }

                if ($part == '(') {
                    if ($isString) {
                        $openBracketCount++;
                    } else {
                        $isString = true;
                    }
                }
            }

            $return .= "\n";
        }

        // substitute the placeholders for the brackets and escape sequences
        $convert = array(
            '##STARTBRACKET##' => '(',
            '##ENDBRACKET##' => ')',
            '##STARTSBRACKET##' => '[',
            '##ENDSBRACKET##' => ']',
            "\\\n" => "\n",
            "\\\r" => "\n",
            "\\\n\r" => "\n",
            "\\\t" => "\t",
            "\\\b" => "\b",
            "\\\f" => "\f",
            '\\\\' => '\\'
        );

        // replace octal character codes
        $text = preg_replace_callback(
            '~\\\\([0-8]{3})~',
            function($matches ) {
                if (octdec($matches[1]) > 32)
                    return utf8_encode(chr(octdec($matches[1])));
                else
                    return "";
            },
            $return
        );

        // execute conversion with $convert
        $text = strtr(($text), $convert);

        if (false !== $this->dest) {
            // store $text into the specified destination file
            // and return true on success or false on error
            return false !== file_put_contents($this->dest);
        } else {
            // return $text
            return $text;
        }
    }
}
