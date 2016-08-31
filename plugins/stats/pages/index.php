<?php
if( rex_post('sendit', 'boolean') ){

    //search_it_stats_saveSettings(rex_post('search_it_stats'));

    $posted_config = rex_post('search_it_stats', [

        ['maxtopSearchitems','int'],
        ['searchtermselect','string'],
        ['searchtermselectmonthcount','int']

    ]);

    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));
    $url = rex::getServer().rex_url::currentBackendPage();
    header('Location: '. $url );
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

$content = array();

$stats = new search_it_stats();
#$stats->createTestData();
#error_reporting(E_ALL);

// general stats
$sql = rex_sql::factory();

$generalstats = $sql->getArray('SELECT
  ((SELECT COUNT(DISTINCT ftable,fid) as count FROM `' . rex::getTablePrefix() . 'searchit_index` WHERE ftable IS NOT NULL) + (SELECT COUNT(DISTINCT fid) as count FROM `' . rex::getTablePrefix() . 'searchit_index` WHERE ftable IS NULL)) AS 010_uniquedatasetcount,
  (SELECT AVG(resultcount) FROM `' . rex::getTablePrefix() . 'searchit_stats_searchterms`) AS 020_averageresultcount,
  (SELECT COUNT(*) FROM `' . rex::getTablePrefix() . 'searchit_stats_searchterms` WHERE resultcount > 0) AS 040_successfullsearchescount,
  (SELECT COUNT(*) FROM `' . rex::getTablePrefix() . 'searchit_stats_searchterms` WHERE resultcount = 0) AS 050_failedsearchescount,
  (SELECT COUNT(DISTINCT term) FROM `' . rex::getTablePrefix() . 'searchit_stats_searchterms`) AS 060_uniquesearchterms'
);
$generalstats = $generalstats[0];
$generalstats['030_searchescount'] = $generalstats['040_successfullsearchescount'] + $generalstats['050_failedsearchescount'];

$generalstats['100_datalength'] = 0;
$generalstats['110_indexlength'] = 0;
foreach ($sql->getArray("SHOW TABLE STATUS LIKE '" . rex::getTablePrefix() . "searchit_%'") as $table) {
    $generalstats['100_datalength'] += $table['Data_length'];
    $generalstats['110_indexlength'] += $table['Index_length'];

    if ($table['Name'] == rex::getTablePrefix() . 'searchit_index') {
        $generalstats['080_searchindexdatalength'] = search_it_stats_bytesize($table['Data_length']);
        $generalstats['090_searchindexindexlength'] = search_it_stats_bytesize($table['Index_length']);
        $generalstats['005_datasetcount'] = $table['Rows'];
    }

    if ($table['Name'] == rex::getTablePrefix() . 'searchit_keywords')
        $generalstats['070_keywordcount'] = $table['Rows'];

    if ($table['Name'] == rex::getTablePrefix() . 'searchit_cache')
        $generalstats['075_cachedsearchcount'] = $table['Rows'];
}

$generalstats['020_averageresultcount'] = number_format($generalstats['020_averageresultcount'], 2, ',', '');
$generalstats['100_datalength'] = search_it_stats_bytesize($generalstats['100_datalength']);
$generalstats['110_indexlength'] = search_it_stats_bytesize($generalstats['110_indexlength']);

ksort($generalstats);

$odd = true;
$table_general = '<dl id="generalstats-list">';
foreach ($generalstats as $key => $value) {
    $table_general .= '<dt class="' . ($odd ? 'odd' : 'even') . '">' . $this->i18n('search_it_stats_generalstats_' . $key) . '</dt><dd class="' . ($odd ? 'odd' : 'even') . '">' . $value . '</dd>';
    $odd = !$odd;
}
$table_general .= '</dl>';

$content['general'] = search_it_getStatSection('generalstats', $this->i18n('search_it_stats_generalstats_title'), $table_general);

// top search terms
$topsearchtermlist = '';
$topsearchtermselect = '<option value="all">' . htmlspecialchars($this->i18n('search_it_stats_searchterm_timestats_title0_all')) . '</option>';
$topsearchterms = $stats->getTopSearchterms($this->getConfig('maxtopSearchitems'));
foreach ($topsearchterms as $term) {
    $topsearchtermlist .= '<li class="' . ($term['success'] == '1' ? 'search_it-stats-success' : 'search_it-stats-fail') . '"><strong>' . htmlspecialchars($term['term']) . '</strong> <em>(' . $term['count'] . ')</em></li>';
    $topsearchtermselect .= '<option value="_' . htmlspecialchars($term['term']) . '"' . (($this->getConfig('searchtermselect') == '_' . $term['term']) ? ' selected="selected"' : '') . '>' . $this->i18n('search_it_stats_searchterm_timestats_title0_single', htmlspecialchars($term['term'])) . '</option>';
}

if (!empty($topsearchterms)) {
    $topsearchtermlist = '<ol>' . $topsearchtermlist . '</ol>';
} else {
    $topsearchtermlist = $this->i18n('search_it_stats_topsearchterms_none');
}
$selectMaxTopSearchitems = '<select name="search_it_stats[maxtopSearchitems]" id="search_it_stats_maxTopSearchitems">';
foreach (array(10, 20, 50, 100, 200, 500, 1000) as $option) {
    $selectMaxTopSearchitems .= '<option value="' . $option . '"' . ((intval($this->getConfig('maxtopSearchitems')) == $option) ? ' selected="selected"' : '') . '>' . $option . '</option>';
}
$selectMaxTopSearchitems .= '</select>';
$pre = $this->i18n('search_it_stats_topsearchterms_title', $selectMaxTopSearchitems, $stats->getSearchtermCount()).
    '<span class="search_it-stats-all">alle</span> <span class="search_it-stats-success">erfolgreich</span> <span class="search_it-stats-fail">fehlgeschlagen</span>';
$content['topsearchterms'] = search_it_getStatSection('topsearchterms', $pre, $topsearchtermlist);

// hit-miss-rate
$content['hit-miss-rate'] = search_it_getStatSection('general', $this->i18n('search_it_stats_general_title'), '
  <img src="index.php?page=search_it/stats&amp;func=image&amp;image=rate_success_failure" alt="' . htmlspecialchars($this->i18n('search_it_stats_rate_success_failure', ' ')) . '" title="' . htmlspecialchars($this->i18n('search_it_stats_rate_success_failure', ' ', $stats->getMissCount() + $stats->getSuccessCount())) . '" />
  <img src="index.php?page=search_it/stats&amp;func=image&amp;image=general_timestats" alt="' . htmlspecialchars($this->i18n('search_it_stats_general_timestats', 6)) . '" title="' . htmlspecialchars($this->i18n('search_it_stats_general_timestats', 6)) . '" />
');

// stats for searchterms over time
if (!empty($topsearchtermselect)){
    $topsearchtermselect = '<select name="search_it_stats[searchtermselect]" id="search_it_stats_searchtermselect">' . $topsearchtermselect . '</select>';
} else {
    $topsearchtermselect = $this->i18n('search_it_stats_searchterm_timestats_title0');
}
$searchtermselectmonthcount = '<select name="search_it_stats[searchtermselectmonthcount]" id="search_it_stats_searchtermselectmonthcount">';
foreach (array(6, 9, 12, 15, 18, 21, 24) as $option) {
    $searchtermselectmonthcount .= '<option value="' . $option . '"' . ((intval($this->getConfig('searchtermselectmonthcount')) == $option) ? ' selected="selected"' : '') . '>' . $option . '</option>';
}
$searchtermselectmonthcount .= '</select>';

$content['searchterm_timestats'] = search_it_getStatSection('searchterm_timestats', $this->i18n('search_it_stats_searchterm_timestats_title', $topsearchtermselect, $searchtermselectmonthcount), '
  <img src="index.php?page=search_it/stats&amp;func=image&amp;image=searchterm_timestats&amp;term=' . htmlspecialchars(urlencode($this->getConfig('searchtermselect') == 'all' ? 'all' : $this->getConfig('searchtermselect'))) . '&amp;monthcount=' . intval($this->getConfig('searchtermselectmonthcount')) . '" 
  alt="' .$this->i18n('search_it_stats_searchterm_timestats_title', $this->getConfig('searchtermselect') == 'all' ? $this->i18n('search_it_stats_searchterm_timestats_title0_all') : $this->i18n('search_it_stats_searchterm_timestats_title0_single', substr($this->getConfig('searchtermselect'), 1)), intval($this->getConfig('searchtermselectmonthcount'))) . '"
   title="' . htmlspecialchars($this->i18n('search_it_stats_searchterm_timestats_title', $this->getConfig('searchtermselect') == 'all' ? $this->i18n('search_it_stats_searchterm_timestats_title0_all') : $this->i18n('search_it_stats_searchterm_timestats_title0_single', substr($this->getConfig('searchtermselect'), 1)), intval($this->getConfig('searchtermselectmonthcount')))) . '" />
');


?>
<script type="text/javascript">
// <![CDATA[

var mainWidth = jQuery('#search_it-form').attr('offsetWidth');
var getonly = 0;

// display links for showing and hiding all sections
jQuery('#search_it-form h2')
    .css('position', 'relative')
    .append(
        jQuery('<div>')
            .css('position', 'absolute')
            .css('top', '0')
            .css('right', '0')
            .css('padding', '5px 1em')
            .css('font-size', '0.75em')
            .css('font-weight', '900')
            .append(
                jQuery('<a><?php echo $this->i18n('search_it_settings_show_all'); ?><' + '/a>')
                    .css('cursor', 'pointer')
                    .css('padding', '0 1em')
                    .click(function () {
                        jQuery.each(jQuery('#search_it-form fieldset'), function (i, elem) {
                            jQuery('.rex-form-wrapper', elem).show();
                        })
                    })
            )
            .append(
                jQuery('<a><?php echo $this->i18n('search_it_settings_show_none'); ?><' + '/a>')
                    .css('cursor', 'pointer')
                    .click(function () {
                        jQuery.each(jQuery('#search_it-form fieldset'), function (i, elem) {
                            jQuery('.rex-form-wrapper', elem).hide();
                        })
                    })
            )
    );


function setLoading(show) {
    if (show) {
        jQuery('#topsearchterms legend')
            .append(
                jQuery('<span class="search_it_loading" >')
            );

        jQuery('#search_it-form legend').each(function (i, elem) {
            var legend = jQuery(elem);
            legend.css('padding-right', (mainWidth - legend.attr('offsetWidth') + parseInt(legend.css('padding-right').replace(/[^0-9]+/, ''))) + 'px');
        });
    } else {
        jQuery('.search_it_loading').remove();

        jQuery('#search_it-form legend').each(function (i, elem) {
            var legend = jQuery(elem);
            legend.css('padding-right', (mainWidth - legend.attr('offsetWidth') + parseInt(legend.css('padding-right').replace(/[^0-9]+/, ''))) + 'px');
        });
    }
}

// top search terms
jQuery('#search_it_stats_maxTopSearchitems').change(function () {
    setLoading(true);
    jQuery.getJSON(
        'index.php?page=search_it/stats&func=topsearchterms&count=' + jQuery('#search_it_stats_maxTopSearchitems').attr('value') + '&only=' + getonly,
        function (data) {
            var selected = jQuery('#search_it_stats_searchtermselect').attr('value');
            var loaddefault = true;

            jQuery('#topsearchterms ol').empty();
            jQuery('#search_it_stats_searchtermselect').empty();

            jQuery('#search_it_stats_searchtermselect').append(
                jQuery('<option value="all">').text('<?php echo htmlspecialchars($this->i18n('search_it_stats_searchterm_timestats_title0_all')); ?>')
            );

            var select = '';
            var cssclass;
            jQuery.each(data, function (i, item) {
                if (item.success == '1')
                    cssclass = 'search_it-stats-success';
                else
                    cssclass = 'search_it-stats-fail';

                // list
                jQuery('#topsearchterms ol').append(
                    jQuery('<li class="' + cssclass + '">').html('<strong>' + item.term + '<' + '/strong> <em>(' + item.count + ')<' + '/em><' + '/li>')
                );

                // select
                if (('_' + item.term) == selected) {
                    select = ' selected="selected"';
                    loaddefault = false;
                }
                else
                    select = '';
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

            setLoading(false);
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

jQuery('#search_it_stats_searchtermselect').change(function () {
    setOverview(jQuery('#search_it_stats_searchtermselect').attr('value'), jQuery('#search_it_stats_searchtermselectmonthcount').attr('value'));
});

jQuery('#search_it_stats_searchtermselectmonthcount').change(function () {
    setOverview(jQuery('#search_it_stats_searchtermselect').attr('value'), jQuery('#search_it_stats_searchtermselectmonthcount').attr('value'));
});

jQuery.each(jQuery('#search_it-form fieldset'), function (i, elem) {
    var legend = jQuery('legend', elem);
    var wrapper = jQuery('.rex-form-wrapper', elem);
    var speed = wrapper.attr('offsetHeight');

    wrapper.hide();

    jQuery(elem)
        .css('border-bottom', '1px solid #fff');

    legend
        .css('cursor', 'pointer')
        .css('padding-right', (mainWidth - legend.attr('offsetWidth') + parseInt(legend.css('padding-right').replace(/[^0-9]+/, ''))) + 'px')
        .css('border-bottom', '1px solid #cbcbcb')
        .mouseover(function () {
            if (wrapper.css('display') == 'none')
                jQuery('legend', elem).css('color', '#aaa');
        })
        .mouseout(function () {
            legend.css('color', '#32353A');
        })
        .click(function () {
            wrapper.slideToggle(speed);
        });
});

// stop event-bubbling for clicks on select-lists
jQuery('#search_it-form legend select,#search_it-form legend span').click(function (event) {
    event.stopPropagation();
});

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
