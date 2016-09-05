<?php
$curDir = __DIR__;
require_once $curDir . '/phplot/phplot.php';

// fetch data
$stats = new search_it_stats();
$missCount = $stats->getMissCount();
$successCount = $stats->getSuccessCount();

$title = $this->i18n('search_it_stats_rate_success_failure', "\n", $missCount+$successCount);
$title = utf8_decode($title);

$lbl_success = $this->i18n('search_it_stats_rate_success_failure_lblsuccess');
$lbl_success = utf8_decode($lbl_success);

$lbl_miss = $this->i18n('search_it_stats_rate_success_failure_lblmiss');
$lbl_miss = utf8_decode($lbl_miss);

$data = array(
  array('', $successCount, $missCount, '', '')
);

ob_clean();
// draw image
$plot = new PHPlot(350, 240);
$plot->SetImageBorderType('none');
$plot->SetTransparentColor('white');
$plot->SetMarginsPixels(NULL,NULL,40,NULL);

$plot->SetPlotType('bars');
$plot->SetDataType('text-data');
$plot->SetDataValues($data);
$plot->SetDataColors(array('#2c8ce0','#14568a'));
$plot->SetShading(6);

# Main plot title:
$plot->SetTitle($title);
$plot->SetFont('title', 3);

$len = strlen(''.($sum = $successCount+$missCount));
$plot->SetYTickIncrement(max(1,ceil($sum/pow(10,$len-1))*pow(10,$len-2)));

# Make a legend for the 3 data sets plotted:
$plot->SetLegend(array($lbl_success.': '.$successCount, $lbl_miss.': '.$missCount));
$plot->SetLegendStyle('left','right');

# Turn off X tick labels and ticks because they don't apply here:
$plot->SetXTickLabelPos('none');
$plot->SetXTickPos('none');
$plot->SetNumberFormat('','');
$plot->SetPrecisionY(0);
$plot->SetPlotAreaWorld(NULL,0,NULL,NULL);

$plot->DrawGraph();
