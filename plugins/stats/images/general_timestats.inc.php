<?php

$stats = new search_it_stats();

$curDir = __DIR__;
require_once $curDir . '/phplot/phplot.php';


// fetch data
$bardata = [];
$cumulateddata = [];
$statCounts = $stats->getCount();

$max = 1;
foreach ($statCounts as $statCount) {
    $bardata[] = array(
        date('M', mktime(0, 0, 0, $statCount['m'], 1, 2010)) . "\n" . $statCount['count'],
        $statCount['count']
    );

    if ($statCount['count'] > $max)
        $max = $statCount['count'];
}

$title = $this->i18n('search_it_stats_general_timestats', 6);
$title = utf8_decode($title);

ob_clean();
$plot = new PHPlot(350, 240);
$plot->SetImageBorderType('none');
$plot->SetTransparentColor('white');
$plot->SetMarginsPixels(NULL,NULL,26,NULL);

# Make sure Y axis starts at 0:
$plot->SetPlotAreaWorld(NULL, 0, NULL, NULL);

$len = strlen(''.$max);
$plot->SetYTickIncrement(max(1,ceil($max/pow(10,$len-1))*pow(10,$len-2)));

# Main plot title:
$plot->SetTitle($title);
$plot->SetFont('title', 3);



// draw bars
$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($bardata);
$plot->SetDataColors(array('#14568a', '#2c8ce0', '#dfe9e9'));
$plot->SetShading(6);
$plot->DrawGraph();
