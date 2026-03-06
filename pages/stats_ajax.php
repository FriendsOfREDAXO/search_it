<?php
$stats = new \FriendsOfRedaxo\SearchIt\Stats\Statistics();

$this->setConfig('maxtopsearchitems', rex_request('count', 'int', 10));

ob_clean();
echo json_encode($stats->getTopSearchterms($this->getConfig('maxtopsearchitems'), rex_request('only', 'int')));

