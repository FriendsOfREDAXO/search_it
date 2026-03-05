<?php

namespace FriendsOfRedaxo\SearchIt\Pdf;

class PdfConverter
{
    private string|false $src;
    private string|false $dest;
    private string|false|null $data = null;

    public function __construct(string|false $src = false, string|false $dest = false)
    {
        $this->src = $src;
        $this->dest = $dest;
    }

    public function setInput(string $data): void
    {
        $this->data = $data;
    }

    public function setSource(string|false $src): void
    {
        $this->src = $src;
    }

    public function setDestination(string|false $dest): void
    {
        $this->dest = $dest;
    }

    public static function directConvert(string $data): string|false
    {
        $converter = new self();
        return $converter->convert($data);
    }

    public function convert(string|false $data = false): string|false
    {
        if (false !== $data) {
            $this->data = $data;
        }

        if (
            (false !== $this->src) and
            (false === $this->data = file_get_contents($this->src))
        ) {
            return false;
        }

        if ($this->data === false || $this->data == null) {
            return false;
        }

        // parse encoding
        preg_match('~/Encoding\s*/(\w+)~ism', $this->data, $encoding);

        $fromEncoding = 'windows-1252';
        if (isset($encoding[1])) {
            switch ($encoding[1]) {
                case 'MacRomanEncoding':
                    $fromEncoding = 'macintosh';
                    break;
                case 'WinAnsiEncoding':
                    break;
            }
        }

        // parse streams
        $isStream = false;
        $stream = '';
        $streams = [];
        $openBracketCount = 0;
        $encodedStream = false;
        foreach (preg_split('~(<<\s*/.*?>>\s*stream\s*)|(\s*endstream\s*)|(\()|(\))~ism', $this->data, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $part) {
            if (preg_match('~<<\s*/(.*?)>>\s*stream\s*~ism', $part, $match)) {
                $switch = 'stream';
                if (false !== strpos($match[1], '/Filter')) {
                    $encodedStream = true;
                }
            } else {
                $switch = trim($part);
            }

            switch ($switch) {
                case '(':
                    if ($isStream and !$encodedStream) {
                        $openBracketCount++;
                    }
                    break;
                case ')':
                    if ($isStream and !$encodedStream) {
                        $openBracketCount--;
                    }
                    break;
                case 'endstream':
                    if ($isStream and $openBracketCount <= 0) {
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
        foreach ($streams as $stream) {
            if (false === $uncompressed = @gzuncompress($stream)) {
                $uncompressed = $stream;
            }

            $uncompressed = @iconv($fromEncoding, 'UTF-8', $uncompressed);

            $text = str_replace(
                ['\(', '\)', '\[', '\]'],
                ['##STARTBRACKET##', '##ENDBRACKET##', '##STARTSBRACKET##', '##ENDSBRACKET##'],
                $uncompressed
            );

            $isTextObj = false;
            $textObject = '';
            $openBracketCount = 0;
            foreach (preg_split('~(\s*BT\s+)|(\s+ET\s+)|(\()|(\))~ism', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $part) {
                $switch = trim($part);
                switch ($switch) {
                    case '(':
                        if ($isTextObj) {
                            $openBracketCount++;
                        }
                        break;
                    case ')':
                        if ($isTextObj) {
                            $openBracketCount--;
                        }
                        break;
                    case 'ET':
                        if ($isTextObj and $openBracketCount <= 0) {
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
            $isString = false;
            $openBracketCount = 0;

            foreach (preg_split('~(?:\s+(Td|TD|Tm|Tj|Tc|Tf|T\*|"|\')\s+)|(\()|(\))~ism', $textObject, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY) as $part) {
                switch ($part) {
                    case 'Td':
                    case 'TD':
                    case 'T*':
                    case '"':
                    case "'":
                        if (!$isString) {
                            $return .= "\n";
                        }
                        break;
                    case ')':
                        if ($isString and $openBracketCount <= 0) {
                            $isString = false;
                            $return .= $string;
                            $string = '';
                        } elseif ($isString) {
                            $openBracketCount--;
                        }
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

        $convert = [
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
            '\\\\' => '\\',
        ];

        $text = preg_replace_callback(
            '~\\\\([0-8]{3})~',
            function ($matches) use ($fromEncoding) {
                if (octdec($matches[1]) > 32) {
                    return mb_convert_encoding(chr(octdec($matches[1])), 'UTF-8', $fromEncoding);
                }
                return '';
            },
            $return
        );

        $text = strtr($text, $convert);

        if (false !== $this->dest) {
            return false !== file_put_contents($this->dest, $text);
        }

        return $text;
    }
}
