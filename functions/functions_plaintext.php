<?php

/**
 * @deprecated Use PlaintextConverter class instead.
 */

use FriendsOfRedaxo\SearchIt\Plaintext\PlaintextConverter;

/** @deprecated Use PlaintextConverter::extensionPointHandler() */
function search_it_doPlaintext($_ep): array
{
    return PlaintextConverter::extensionPointHandler($_ep);
}

/** @deprecated Use PlaintextConverter::convert() */
function search_it_getPlaintext($_text, $_remove): string
{
    return PlaintextConverter::convert($_text, $_remove);
}
