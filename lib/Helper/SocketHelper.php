<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex;
use rex_addon;
use rex_socket;
use rex_version;

/**
 * Direct-connection socket: bypasses the global socket_proxy setting from config.yml.
 *
 * Used only for destinations listed in the addon's `proxy_ignore` setting.
 */
class SearchItDirectSocket extends rex_socket
{
}

class SocketHelper
{
    /**
     * Normalize a host name or IP literal for comparison:
     * lowercase, trimmed, without surrounding brackets (IPv6 literals).
     */
    private static function normalizeHost(string $host): string
    {
        $host = trim(strtolower($host));
        return trim($host, '[]');
    }

    /**
     * Return the normalized, lowercase list of hostnames from the proxy_ignore setting.
     *
     * @return list<string>
     */
    public static function getProxyIgnoreList(): array
    {
        $raw = rex_addon::get('search_it')->getConfig('proxy_ignore', 'localhost,127.0.0.1,::1');
        $raw = trim((string) $raw);
        if ($raw === '') {
            return [];
        }
        $list = array_filter(array_map('trim', explode(',', $raw)));
        $list = array_map([self::class, 'normalizeHost'], $list);
        return array_filter($list);
    }

    /**
     * Check whether the destination of $scanurl is listed in the proxy_ignore
     * setting. Such destinations are connected directly, bypassing the global
     * socket_proxy setting - typically for internal names/addresses
     * (e.g. "localhost", "127.0.0.1") that the proxy cannot resolve or reach.
     */
    public static function isProxyIgnored(string $scanurl): bool
    {
        $host = self::normalizeHost((string) parse_url($scanurl, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        $ignored = self::getProxyIgnoreList();
        return in_array($host, $ignored, true);
    }

    /**
     * Check if URL uses a valid HTTP or HTTPS scheme for socket connections.
     */
    public static function isValidHttpScheme(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        return in_array(strtolower($scheme ?? ''), ['http', 'https']);
    }

    /**
     * Prepare a rex_socket for scanning a URL during indexing.
     *
     * Hosts listed in the proxy_ignore setting are connected directly
     * (bypassing the global socket_proxy setting); everything else uses the
     * regular core socket, which applies the global socket_proxy config.
     */
    public static function prepareSocket(string $scanurl): rex_socket
    {
        $socket = self::isProxyIgnored($scanurl)
            ? SearchItDirectSocket::factoryURL($scanurl)
            : rex_socket::factoryURL($scanurl);
        if (rex_version::compare(rex::getVersion(), '5.13', '>=')) {
            $socket->setOptions([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]);
        }
        if (rex_addon::get('search_it')->getConfig('htaccess_user') != '' && rex_addon::get('search_it')->getConfig('htaccess_pass') != '') {
            $socket->addBasicAuthorization(
                rex_addon::get('search_it')->getConfig('htaccess_user'),
                rex_addon::get('search_it')->getConfig('htaccess_pass')
            );
        }

        return $socket;
    }
}
