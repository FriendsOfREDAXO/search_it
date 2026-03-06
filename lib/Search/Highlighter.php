<?php

namespace FriendsOfRedaxo\SearchIt\Search;

use rex_addon;
use rex_request;

class Highlighter
{
    public static function outputFilter(\rex_extension_point $ep): string
    {
        $subject = $ep->getSubject();
        $searchTerms = rex_request('search_highlighter', 'string', '');

        $si = rex_addon::get('search_it');

        $tag = 'span';
        if ('' != $si->getConfig('highlightertag')) {
            $tag = str_replace(['<', '>'], '', $si->getConfig('highlightertag'));
        }
        $begin = '<' . $tag . " class='" . $si->getConfig('highlighterclass') . "'>";
        $end = '</' . $tag . '>';

        $tags = [$begin, $end];

        preg_match('/<body[^>]*>(.*?)<\/body>/is', $subject, $matches);

        $body = self::highlightText($matches[0], $searchTerms, $tags);
        $subject = preg_replace('/<body[^>]*>(.*?)<\/body>/is', $body, $subject);

        return $subject;
    }

    public static function highlightText(string $subject, string $searchString, array $tags): string
    {
        preg_match_all('~(?:(\+*)"([^"]*)")|(?:(\+*)(\S+))~is', $searchString, $matches, PREG_SET_ORDER);

        $searchterms = [];
        foreach ($matches as $match) {
            if (count($match) == 5) {
                $word = $match[4];
            } elseif (!empty($match[2])) {
                $word = $match[2];
            } else {
                continue;
            }
            $searchterms[] = preg_quote($word, '~');
        }

        $hidemask = '7341fqtb99';
        $all = preg_replace_callback(
            '~<[^<]*?(?:=\'[^\']*?|="[^"]*?)(' . implode('|', $searchterms) . ')[^"\']*?(?:"|\')~',
            function ($match) {
                $hidemask = '7341fqtb99';
                return str_replace($match[1], $hidemask . $match[1], $match[0]);
            },
            $subject
        );

        $all = preg_replace('~(?<!<|' . $hidemask . ')(' . implode('|', $searchterms) . ')(?![^<]*\>)~ims', $tags[0] . '$1' . $tags[1], $all);
        $all = str_replace($hidemask, '', $all);

        $all = preg_replace_callback(
            '~(<(script|style)[^>]*>)(.*?)(</\2>)~is',
            function ($match) use ($tags) {
                $content = str_replace([$tags[0], $tags[1]], '', $match[3]);
                return $match[1] . $content . $match[4];
            },
            $all
        );

        return $all;
    }
}
