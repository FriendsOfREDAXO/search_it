<?php

namespace FriendsOfRedaxo\SearchIt\Plaintext;

use rex_addon;
use simple_html_dom;

class PlaintextConverter
{
    public static function extensionPointHandler(\rex_extension_point $ep): array
    {
        $subject = $ep->getSubject();
        $subject = self::convert($subject, preg_replace('~\s+~ism', ' ', rex_addon::get('search_it')->getConfig('selectors') ?? ''));

        return ['text' => $subject, 'process' => !empty(rex_addon::get('search_it')->getConfig('processparent'))];
    }

    public static function convert(string $text, string $remove): string
    {
        $pt = rex_addon::get('search_it');
        foreach (explode(',', ($pt->getConfig('plainOrder') ?? '')) as $elem) {
            switch ($elem) {
                case 'selectors':
                    $html = new simple_html_dom();
                    $html->load($text);
                    $html->remove($remove);
                    $html->load($html->outertext);
                    $text = $html->plaintext;
                    break;

                case 'regex':
                    if (!empty($pt->getConfig('regex'))) {
                        $regex = [];
                        $replacement = [];
                        $odd = true;
                        foreach (explode("\n", $pt->getConfig('regex')) as $line) {
                            if ($line != '') {
                                if ($odd) {
                                    $regex[] = trim($line);
                                } else {
                                    $replacement[] = $line;
                                }
                                $odd = !$odd;
                            }
                        }
                        $text = preg_replace($regex, $replacement, $text);
                    }
                    break;

                case 'textile':
                    if (!empty($pt->getConfig('textile')) and function_exists('rex_textile::parse')) {
                        $text = \rex_textile::parse($text);
                    }
                    break;

                case 'striptags':
                    if (!empty($pt->getConfig('striptags'))) {
                        $text = strip_tags($text);
                    }
                    break;
            }
        }

        return $text;
    }
}
