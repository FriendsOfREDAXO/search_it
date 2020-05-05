<?php

if( rex_post('sendit', 'boolean') ){

    $posted_config = rex_post('search_it_stats', [

        ['maxtopsearchitems','int'],
        ['searchtermselect','string'],
        ['searchtermselectmonthcount','int']

    ]);

    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));

}


$func = rex_request('func', 'string');

if (!empty($func)) {
    switch ($func) {
        case 'image':
            require $this->getPath('images').'/'. rex_request('image', 'string') . '.inc.php';
            exit;
            break;

        case 'topsearchterms':
            require 'ajax.php';
            exit;
            break;
    }

}

$content = [];

$content[] = search_it_getSettingsFormSection(
    'search_it_stats_description',
    $this->i18n('search_it_stats_description_title'),
    array(
        array(
            'type' => 'directoutput',
            'output' => ''
        )
    ),false, false
);

$content[] =  '<div id="stats_elements">';

$stats = new search_it_stats();
#$stats->createTestData();
#error_reporting(E_ALL);

// general stats
$sql = rex_sql::factory();

$generalstats = $sql->getArray('SELECT
  ((SELECT COUNT(DISTINCT ftable,fid) as count FROM `' . rex::getTablePrefix() . rex::getTempPrefix().'search_it_index` WHERE ftable IS NOT NULL) + (SELECT COUNT(DISTINCT fid) as count FROM `' . rex::getTablePrefix() .rex::getTempPrefix(). 'search_it_index` WHERE ftable IS NULL)) AS 010_uniquedatasetcount,
  (SELECT AVG(resultcount) FROM `' . rex::getTablePrefix() . 'search_it_stats_searchterms`) AS 020_averageresultcount,
  (SELECT COUNT(*) FROM `' . rex::getTablePrefix(). 'search_it_stats_searchterms` WHERE resultcount > 0) AS 040_successfullsearchescount,
  (SELECT COUNT(*) FROM `' . rex::getTablePrefix() . 'search_it_stats_searchterms` WHERE resultcount = 0) AS 050_failedsearchescount,
  (SELECT COUNT(DISTINCT term) FROM `' . rex::getTablePrefix(). 'search_it_stats_searchterms`) AS 060_uniquesearchterms'
);
$generalstats = $generalstats[0];
$generalstats['030_searchescount'] = $generalstats['040_successfullsearchescount'] + $generalstats['050_failedsearchescount'];

$generalstats['100_datalength'] = 0;
$generalstats['110_indexlength'] = 0;
foreach ($sql->getArray("SHOW TABLE STATUS LIKE '" . rex::getTablePrefix() .rex::getTempPrefix(). "search_it_%'") as $table) {
    $generalstats['100_datalength'] += $table['Data_length'];
    $generalstats['110_indexlength'] += $table['Index_length'];

    if ($table['Name'] == rex::getTablePrefix() .rex::getTempPrefix(). 'search_it_index') {
        $generalstats['080_searchindexdatalength'] = search_it_stats_bytesize($table['Data_length']);
        $generalstats['090_searchindexindexlength'] = search_it_stats_bytesize($table['Index_length']);
        $generalstats['005_datasetcount'] = $table['Rows'];
    }

    if ($table['Name'] == rex::getTablePrefix().rex::getTempPrefix() . 'search_it_keywords')
        $generalstats['070_keywordcount'] = $table['Rows'];

    if ($table['Name'] == rex::getTablePrefix().rex::getTempPrefix() . 'search_it_cache')
        $generalstats['075_cachedsearchcount'] = $table['Rows'];
}

$generalstats['020_averageresultcount'] = number_format($generalstats['020_averageresultcount'], 2, ',', '');
$generalstats['100_datalength'] = search_it_stats_bytesize($generalstats['100_datalength']);
$generalstats['110_indexlength'] = search_it_stats_bytesize($generalstats['110_indexlength']);

ksort($generalstats);


$table_general = '<table id="generalstats-list" class="table">';
foreach ($generalstats as $key => $value) {
    $table_general .= '<tr><td>' . $this->i18n('search_it_stats_generalstats_' . $key) . '</td><td>' . $value . '</td></tr>';
}
$table_general .= '</table>';

$content[] = search_it_getSettingsFormSection(
    'generalstats',
    $this->i18n('search_it_stats_generalstats_title'),
    array(
        array(
            'type' => 'directoutput',
            'output' => $table_general
        )
    ), 'info', true
);


// top search terms
$topsearchtermlist = '';
$topsearchtermselect = '<option value="all" '. ($this->getConfig('searchtermselect') == 'all' ? ' selected="selected"' : '') .'>' . rex_escape($this->i18n('search_it_stats_searchterm_timestats_title0_all')) . '</option>';
$topsearchterms = $stats->getTopSearchterms($this->getConfig('maxtopsearchitems'));
foreach ($topsearchterms as $term) {
    $topsearchtermlist .= '<li class="' . ($term['success'] == '1' ? 'search_it-stats-success text-success' : 'search_it-stats-fail text-danger') . '"><strong>' . rex_escape($term['term']) . '</strong> <em>(' . $term['count'] . ')</em></li>';
    $topsearchtermselect .= '<option value="_' . rex_escape($term['term']) . '"' . (($this->getConfig('searchtermselect') == '_' . $term['term']) ? ' selected="selected"' : '') . '>' . $this->i18n('search_it_stats_searchterm_timestats_title0_single', rex_escape($term['term'])) . '</option>';
}

if (!empty($topsearchterms)) {
    $topsearchtermlist = '<ol>' . $topsearchtermlist . '</ol>';
} else {
    $topsearchtermlist = $this->i18n('search_it_stats_topsearchterms_none');
}
$selectMaxTopSearchitems = '<select name="search_it_stats[maxtopsearchitems]" id="search_it_stats_maxTopSearchitems" class="form-control">';
foreach (array(10, 20, 50, 100, 200, 500, 1000) as $option) {
    $selectMaxTopSearchitems .= '<option value="' . $option . '"' . (max(intval($this->getConfig('maxtopsearchitems')),10) == $option ? ' selected="selected"' : '') . '>' . $option . '</option>';
}
$selectMaxTopSearchitems .= '</select>';
$pre = rex_i18n::rawMsg('search_it_stats_topsearchterms_title', $selectMaxTopSearchitems, $stats->getSearchtermCount());
$pre2 = '<div class="btn-group" role="group"><span class="search_it-stats-all btn btn-default">alle</span> <span class="search_it-stats-success btn btn-success">erfolgreich</span> <span class="search_it-stats-fail btn btn-danger">fehlgeschlagen</span></div>';


$content[] = search_it_getSettingsFormSection(
    'topsearchterms',
    $this->i18n('search_it_stats_topsearchterms_titletitle'),
    array(
        array(
            'type' => 'directoutput',
            'output' => $pre,
        ),
        array(
            'type' => 'directoutput',
            'output' => $pre2
        ),
        array(
            'type' => 'directoutput',
            'output' => $topsearchtermlist
        )
    ), 'info', true
);


$content[] = search_it_getSettingsFormSection(
    'general',
    $this->i18n('search_it_stats_general_title'),
    array(
        array(
            'type' => 'directoutput',
            'output' => '
                          <img src="index.php?page=search_it/stats&amp;func=image&amp;image=rate_success_failure" alt="' . rex_escape($this->i18n('search_it_stats_rate_success_failure', ' ')) . '" title="' . rex_escape($this->i18n('search_it_stats_rate_success_failure', ' ', $stats->getMissCount() + $stats->getSuccessCount())) . '" />
                          <img src="index.php?page=search_it/stats&amp;func=image&amp;image=general_timestats" alt="' . rex_escape($this->i18n('search_it_stats_general_timestats', 6)) . '" title="' . rex_escape($this->i18n('search_it_stats_general_timestats', 6)) . '" />
                        '
        )
    ), 'info', true

);


// stats for searchterms over time
if (!empty($topsearchtermselect)){
    $topsearchtermselect = '<select name="search_it_stats[searchtermselect]" id="search_it_stats_searchtermselect" class="form-control" >' . $topsearchtermselect . '</select>';
} else {
    $topsearchtermselect = $this->i18n('search_it_stats_searchterm_timestats_title0');
}
$searchtermselectmonthcount = '<select name="search_it_stats[searchtermselectmonthcount]" id="search_it_stats_searchtermselectmonthcount" class="form-control" >';
foreach (array(6, 9, 12, 15, 18, 21, 24) as $option) {
    $searchtermselectmonthcount .= '<option value="' . $option . '"' . ((intval($this->getConfig('searchtermselectmonthcount')) == $option) ? ' selected="selected"' : '') . '>' . $option . '</option>';
}
$searchtermselectmonthcount .= '</select>';

$pre = rex_i18n::rawMsg('search_it_stats_searchterm_timestats_title', $topsearchtermselect, $searchtermselectmonthcount);
$rest = '<img src="index.php?page=search_it/stats&amp;func=image&amp;image=searchterm_timestats&amp;term='
    . rex_escape(urlencode($this->getConfig('searchtermselect') == 'all' ? 'all' : $this->getConfig('searchtermselect')))
    . '&amp;monthcount=' . intval($this->getConfig('searchtermselectmonthcount')) . '"  alt="'
    . $this->i18n('search_it_stats_searchterm_timestats_title', $this->getConfig('searchtermselect') == 'all' ? $this->i18n('search_it_stats_searchterm_timestats_title0_all') : $this->i18n('search_it_stats_searchterm_timestats_title0_single', substr($this->getConfig('searchtermselect'), 1)), intval($this->getConfig('searchtermselectmonthcount'))) . '"'
    .' title="' . rex_escape($this->i18n('search_it_stats_searchterm_timestats_title', $this->getConfig('searchtermselect') == 'all' ? $this->i18n('search_it_stats_searchterm_timestats_title0_all') : $this->i18n('search_it_stats_searchterm_timestats_title0_single', substr($this->getConfig('searchtermselect'), 1)), intval($this->getConfig('searchtermselectmonthcount')))) . '" />';

$content[] = search_it_getSettingsFormSection(
    'searchterm_timestats',
    $this->i18n('search_it_stats_searchterm_timestats_titletitle'),
    array(
        array(
            'type' => 'directoutput',
            'output' => $pre
        ),
        array(
            'type' => 'directoutput',
            'output' => $rest
        )
    ), 'info', true
);
$content[] =  '</div>';
?>
<script type="text/javascript">
// <![CDATA[
(function(jQuery) {
    jQuery(document).ready(function () {

    // display links for showing and hiding all sections
    jQuery('#search_it_stats_description dl').first()
        .css('position', 'relative')
        .append(
            jQuery('<dt>')
                .css('font-weight', '900')
                .css('margin-bottom','1em')
                .append(
                    jQuery('<a><?php echo $this->i18n('search_it_settings_show_all'); ?><' + '/a>')
                        .css('cursor', 'pointer')
                        .css('padding', '0 1em')
                        .click(function () {
                            jQuery('#stats_elements .panel-collapse').collapse('show');
                        })
                )
                .append(
                    jQuery('<a><?php echo $this->i18n('search_it_settings_show_none'); ?><' + '/a>')
                        .css('cursor', 'pointer')
                        .click(function () {
                            jQuery('#stats_elements .panel-collapse').collapse('hide');
                        })
                )
        );


    // top search terms
    var getonly = 0;
    jQuery('#search_it_stats_maxTopSearchitems').change(function(){

        jQuery.getJSON(
            'index.php?page=search_it/stats&func=topsearchterms&count=' + jQuery('#search_it_stats_maxTopSearchitems').val() + '&only=' + getonly,
            function (data) {

                var selected = jQuery('#search_it_stats_searchtermselect').val();
                var loaddefault = true;

                jQuery('#topsearchterms ol').empty();
                jQuery('#search_it_stats_searchtermselect').empty();

                jQuery('#search_it_stats_searchtermselect').append(
                    jQuery('<option value="all">').text('<?php echo rex_escape($this->i18n('search_it_stats_searchterm_timestats_title0_all')); ?>')
                );

                var select = '';
                var cssclass;
                jQuery.each(data, function (i, item) {
                    if (item.success == '1') {
                        cssclass = 'search_it-stats-success text-success';
                    } else {
                        cssclass = 'search_it-stats-fail text-danger';
                    }
                    // list
                    jQuery('#topsearchterms ol').append(
                        jQuery('<li class="' + cssclass + '">').html('<strong>' + item.term + '<' + '/strong> <em>(' + item.count + ')<' + '/em><' + '/li>')
                    );

                    // select
                    if (('_' + item.term) == selected) {
                        select = ' selected="selected"';
                        loaddefault = false;
                    } else {
                        select = '';
                    }
                    jQuery('#search_it_stats_searchtermselect').append(
                        jQuery('<option value="_' + item.term + '"' + select + '>').text('"' + item.term + '"')
                    );
                });

                if (loaddefault) {
                    date = new Date();
                    jQuery('#searchterm_timestats img').attr(
                        'src',
                        'index.php?page=search_it/stats&func=image&image=searchterm_timestats&term=all&monthcount=<?php echo $this->getConfig('searchtermselectmonthcount'); ?>&time=' + Date.parse(date)
                    );
                }

            }
        );
    });
    jQuery('span.search_it-stats-all').click(function () {
        getonly = 0;
        jQuery('#search_it_stats_maxTopSearchitems').change();
    });
    jQuery('span.search_it-stats-success').click(function () {
        getonly = 1;
        jQuery('#search_it_stats_maxTopSearchitems').change();
    });
    jQuery('span.search_it-stats-fail').click(function () {
        getonly = 2;
        jQuery('#search_it_stats_maxTopSearchitems').change();
    });


    // search term time stats
    function setOverview(term, count) {
        date = new Date();

        jQuery('#searchterm_timestats img').attr(
            'src',
            'index.php?page=search_it/stats&func=image&image=searchterm_timestats&term=' + term + '&monthcount=' + count + '&time=' + Date.parse(date)
        );
    }
    jQuery('#search_it_stats_searchtermselect, #search_it_stats_searchtermselectmonthcount').change(function () {
        setOverview(jQuery('#search_it_stats_searchtermselect').val(), jQuery('#search_it_stats_searchtermselectmonthcount').val());
    });


    });
}(jQuery));
// ]]>
</script>
<?php
$content = implode( "\n", $content);

$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="sendit" value="1" ' . rex::getAccesskey($this->i18n('search_it_settings_submitbutton'), 'save') . '>' . $this->i18n('search_it_settings_submitbutton') . '</button>';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('title', $this->i18n('search_it_stats_title'),'');
$fragment->setVar('class', 'info', false);
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);

echo '<form method="post" action="'. rex_url::currentBackendPage() .'" id="search_it_stats_form">';
echo $fragment->parse('core/page/section.php');
echo '</form>';
