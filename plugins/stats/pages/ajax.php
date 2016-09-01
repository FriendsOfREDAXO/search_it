<?php
$stats = new search_it_stats();

$this->setConfig('maxtopsearchitems', rex_request('count','int',10));

ob_clean();
echo json_encode($stats->getTopSearchterms($this->getConfig('maxtopsearchitems'), rex_request('only','int')));

