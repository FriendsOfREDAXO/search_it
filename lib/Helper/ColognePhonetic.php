<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

class ColognePhonetic
{
    /**
     * Phonetik für die deutsche Sprache nach dem Kölner Verfahren.
     *
     * @see https://github.com/deezaster/germanphonetic
     * @author Andy Theiler <andy@x3m.ch>
     * @license BSD License
     */
    public static function encode(string $word): string
    {
        $code = '';
        $word = strtolower($word);

        if (mb_strlen($word) < 1) {
            return '';
        }

        $word = str_replace(
            ['ç', 'v', 'w', 'j', 'y', 'ph', 'ä', 'ö', 'ü', 'ß', 'é', 'è', 'ê', 'à', 'á', 'â', 'ë'],
            ['c', 'f', 'f', 'i', 'i', 'f', 'a', 'o', 'u', 'ss', 'e', 'e', 'e', 'a', 'a', 'a', 'e'],
            $word
        );

        $word = preg_replace('/[^a-zA-Z]/', '', $word);

        $wordlen = mb_strlen($word);
        if (!$wordlen > 0) {
            return '';
        }

        $char = mb_str_split($word);

        if ($char[0] == 'c') {
            if ($wordlen == 1) {
                $code = 8;
                $x = 1;
            } else {
                switch ($char[1]) {
                    case 'a':
                    case 'h':
                    case 'k':
                    case 'l':
                    case 'o':
                    case 'q':
                    case 'r':
                    case 'u':
                    case 'x':
                        $code = '4';
                        break;
                    default:
                        $code = '8';
                        break;
                }
                $x = 1;
            }
        } else {
            $x = 0;
        }
        for (; $x < $wordlen; $x++) {
            switch ($char[$x]) {
                case 'a':
                case 'e':
                case 'i':
                case 'o':
                case 'u':
                    $code .= '0';
                    break;
                case 'b':
                case 'p':
                    $code .= '1';
                    break;
                case 'd':
                case 't':
                    if ($x + 1 < $wordlen) {
                        switch ($char[$x + 1]) {
                            case 'c':
                            case 's':
                            case 'z':
                                $code .= '8';
                                break;
                            default:
                                $code .= '2';
                                break;
                        }
                    } else {
                        $code .= '2';
                    }
                    break;
                case 'f':
                    $code .= '3';
                    break;
                case 'g':
                case 'k':
                case 'q':
                    $code .= '4';
                    break;
                case 'c':
                    if ($x + 1 < $wordlen) {
                        switch ($char[$x + 1]) {
                            case 'a':
                            case 'h':
                            case 'k':
                            case 'o':
                            case 'q':
                            case 'u':
                            case 'x':
                                switch ($char[$x - 1]) {
                                    case 's':
                                    case 'z':
                                        $code .= '8';
                                        break;
                                    default:
                                        $code .= '4';
                                }
                                break;
                            default:
                                $code .= '8';
                                break;
                        }
                    } else {
                        $code .= '8';
                    }
                    break;
                case 'x':
                    if ($x > 0) {
                        switch ($char[$x - 1]) {
                            case 'c':
                            case 'k':
                            case 'q':
                                $code .= '8';
                                break;
                            default:
                                $code .= '48';
                                break;
                        }
                    } else {
                        $code .= '48';
                    }
                    break;
                case 'l':
                    $code .= '5';
                    break;
                case 'm':
                case 'n':
                    $code .= '6';
                    break;
                case 'r':
                    $code .= '7';
                    break;
                case 's':
                case 'z':
                    $code .= '8';
                    break;
            }
        }

        $code = preg_replace("/(.)\\1+/", "\\1", $code);
        $codelen = mb_strlen($code);

        $num = mb_str_split($code);
        $phoneticcode = $num[0] ?? 0;

        for ($x = 1; $x < $codelen; $x++) {
            if ($num[$x] != '0') {
                $phoneticcode .= $num[$x];
            }
        }

        return $phoneticcode;
    }
}
