<?php

namespace FriendsOfRedaxo\SearchIt\Plaintext;

use DOMDocument;
use DOMXPath;
use rex_addon;

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
            switch (trim($elem)) {
                case 'selectors':
                    $text = self::removeBySelectors($text, $remove);
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
                        $text = preg_replace('~</?(address|blockquote|br|center|del|dir|div|dl|fieldset|form|h1|h2|h3|h4|h5|h6|hr|ins|isindex|li|menu|noframes|noscript|ol|p|pre|table|td|th|tr|ul)[^>]*>~siu', ' ', $text);
                        $text = strip_tags($text);
                        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
                        $text = preg_replace('~\s+~', ' ', $text);
                    }
                    break;
            }
        }

        return $text;
    }

    /**
     * Remove HTML elements matching CSS selectors using DOMDocument.
     */
    private static function removeBySelectors(string $html, string $selectors): string
    {
        if (empty(trim($selectors)) || empty(trim($html))) {
            return $html;
        }

        $doc = new DOMDocument();
        // Suppress warnings for malformed HTML, use UTF-8
        @$doc->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        $xpath = new DOMXPath($doc);

        foreach (explode(',', $selectors) as $selector) {
            $selector = trim($selector);
            if ($selector === '') {
                continue;
            }

            $xpathExpr = self::cssToXPath($selector);
            if ($xpathExpr === null) {
                continue;
            }

            $nodes = $xpath->query($xpathExpr);
            if ($nodes === false) {
                continue;
            }

            // Collect nodes first, then remove (avoid modifying DOM during iteration)
            $toRemove = [];
            foreach ($nodes as $node) {
                $toRemove[] = $node;
            }
            foreach ($toRemove as $node) {
                $node->parentNode->removeChild($node);
            }
        }

        $result = $doc->saveHTML();
        // Remove the XML encoding declaration we added
        $result = preg_replace('~<\?xml encoding="UTF-8"\>~', '', $result);

        return trim($result);
    }

    /**
     * Convert a simple CSS selector to an XPath expression.
     *
     * Supports: tag, .class, #id, tag.class, tag#id,
     * [attr], [attr=value], tag[attr], descendant selectors (space),
     * and combinations thereof.
     */
    private static function cssToXPath(string $selector): ?string
    {
        $selector = trim($selector);
        if ($selector === '') {
            return null;
        }

        // Handle descendant selectors (space-separated parts)
        $parts = preg_split('~\s+~', $selector);
        if (count($parts) > 1) {
            $xpathParts = [];
            foreach ($parts as $part) {
                $converted = self::cssSingleToXPath($part);
                if ($converted === null) {
                    return null;
                }
                $xpathParts[] = $converted;
            }
            return '//' . implode('//', $xpathParts);
        }

        $converted = self::cssSingleToXPath($selector);
        return $converted !== null ? '//' . $converted : null;
    }

    /**
     * Convert a single CSS selector (no spaces) to XPath.
     */
    private static function cssSingleToXPath(string $selector): ?string
    {
        // Parse: tag, #id, .class, [attr], [attr=value] and combinations
        $tag = '*';
        $conditions = [];

        // Extract tag name (must be first if present)
        if (preg_match('~^([a-zA-Z][a-zA-Z0-9]*)~', $selector, $m)) {
            $tag = $m[1];
            $selector = substr($selector, strlen($m[1]));
        }

        // Extract #id
        while (preg_match('~^#([a-zA-Z0-9_-]+)~', $selector, $m)) {
            $conditions[] = '@id="' . $m[1] . '"';
            $selector = substr($selector, strlen($m[0]));
        }

        // Extract .class (multiple allowed)
        while (preg_match('~^\.([a-zA-Z0-9_-]+)~', $selector, $m)) {
            $conditions[] = 'contains(concat(" ", normalize-space(@class), " "), " ' . $m[1] . ' ")';
            $selector = substr($selector, strlen($m[0]));
        }

        // Extract [attr] and [attr=value]
        while (preg_match('~^\[([a-zA-Z0-9_-]+)(?:=["\']?([^"\'\]]*)["\']?)?\]~', $selector, $m)) {
            if (isset($m[2])) {
                $conditions[] = '@' . $m[1] . '="' . $m[2] . '"';
            } else {
                $conditions[] = '@' . $m[1];
            }
            $selector = substr($selector, strlen($m[0]));
        }

        if (!empty($conditions)) {
            return $tag . '[' . implode(' and ', $conditions) . ']';
        }

        return $tag;
    }
}
