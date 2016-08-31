<?php
$stats = new search_it_stats();

$this->setConfig('maxtopSearchitems', rex_request('count','int',10));

//search_it_stats_saveSettings($this->getConfig());

echo json_encode($stats->getTopSearchterms($this->getConfig('maxtopSearchitems'), rex_request('only','int',0)));
