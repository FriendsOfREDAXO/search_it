<?php

namespace FriendsOfRedaxo\SearchIt\Helper;

use rex;
use rex_addon;
use rex_socket;
use rex_version;

class SocketHelper
{
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
     */
    public static function prepareSocket(string $scanurl): rex_socket
    {
        $socket = rex_socket::factoryURL($scanurl);
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
