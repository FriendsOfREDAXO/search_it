<?php
$this->setConfig('searchtermselect', rex_get('term', 'string', ''));
$this->setConfig('searchtermselectmonthcount', rex_get('monthcount', 'int', 12));


if ($this->getConfig('searchtermselect') == 'all') {
    $term = '';
} else {
    $term = substr($this->getConfig('searchtermselect'), 1);
}


$stats = new search_it_stats();

$curDir = __DIR__;
require_once $curDir . '/phplot/phplot.php';

// fetch data
$bardata = [];
$cumulateddata = [];

$max = 1;
foreach ( $stats->getTimestats($term, $this->getConfig('searchtermselectmonthcount')) as $month) {
    $bardata[] = array(
        date('M', mktime(0, 0, 0, $month['m'], 1, 2010)) . "\n" . $month['count'],
        $month['count']
    );

    if ($month['count'] > $max)
        $max = $month['count'];
}

$title = $this->i18n(
    'search_it_stats_searchterm_timestats_title',
    empty($term)
        ? $this->i18n('search_it_stats_searchterm_timestats_title0_all')
        : $this->i18n(
        'search_it_stats_searchterm_timestats_title0_single',
        $term
    ),
    rex_get('monthcount','int')
);
$title = utf8_decode($title);


ob_clean();
// draw bars
$plot = new PHPlot(700, 240);
$plot->SetImageBorderType('none');
$plot->SetTransparentColor('white');
$plot->SetMarginsPixels(NULL, NULL, 26, NULL);

# Make sure Y axis starts at 0:
$plot->SetPlotAreaWorld(NULL, 0, NULL, NULL);

$len = strlen('' . $max);
$plot->SetYTickIncrement(max(1, ceil($max / pow(10, $len - 1)) * pow(10, $len - 2)));

# Main plot title:
$plot->SetTitle($title);
$plot->SetFont('title', 3);

// draw bars
$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($bardata);
$plot->SetDataColors(array('#14568a', '#2c8ce0', '#dfe9e9'));
$plot->SetShading(ceil(48 / $this->getConfig('searchtermselectmonthcount')));
$plot->DrawGraph();

