<?php

function search_it_getArticleIds($cats = false) {
    $whereCats = [];
    if(is_array($cats)){
        foreach($cats as $catID) {
            $whereCats[] = "path LIKE '%|" . $catID . "|%'";
        }
    }

    $return = [];
    $query = 'SELECT id FROM '.rex::getTable('article');
    if( !rex_addon::get('search_it')->getConfig('indexoffline') ) {
        $query .= ' WHERE status = 1';
    }
    if(!empty($whereCats)) {
        $query .= ' AND ('.implode(' OR ', $whereCats).' OR (id IN ('.implode(',',$cats).') AND startarticle = 1))';
    }
    $query .= ' GROUP BY id ORDER BY id';

    $sql = rex_sql::factory();
    foreach($sql->getArray($query) as $art) {
        $return[] = $art['id'];
    }

    return $return;
}


if ( !empty(rex_get('do')) AND rex_get('do') == 'incremental') {

    $content  = '<div style="display:none;" id="search_it_generate_cancel"><p class="alert alert-warning">' . $this->i18n('search_it_generate_cancel') . '</p></div>';
    $content .= '<div style="display:none;" id="search_it_generate_done"><p class="alert alert-success">' . $this->i18n('search_it_generate_done') . '</p></div>';
    $content .= '<div id="search_it_generate_inprogress"><p class="alert alert-warning" >' . $this->i18n('search_it_generate_inprogress') . '</p></div>';
    //$content .= '<div id="search_it_generate_header">'.$this->i18n('search_it_generate_incremental').'</div>';
    $content .= '<div id="search_it_generate_header"></div><div id="search_it_generate_log"></div>';

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('search_it_generate_incremental'), false);
    $fragment->setVar('body', $content , false);
    echo $fragment->parse('core/page/section.php');

    $js_output = '';
    $globalcount = 0;

	foreach(search_it_getArticleIds() as $id) {
        #$js_output .= 'index("art",'.$id.');';
        $js_output .= 'indexArray.push(new Array("art",'.$id.'));';
        $globalcount++;
    }

	// index url 2 addon URLs
	if(rex_addon::get('search_it')->getConfig('index_url_addon') && search_it_isUrlAddOnAvailable()) {
		$url_sql = rex_sql::factory();
		$url_sql->setTable(search_it_getUrlAddOnTableName());
		if ($url_sql->select('url_hash')) {
			foreach ($url_sql->getArray() as $url) {
		        $js_output .= 'indexArray.push(new Array("url","'. $url['url_hash'] .'"));';
				$globalcount++;
			}
		}
	}

    if(!empty($this->getConfig('include')) AND is_array($this->getConfig('include'))) {
        foreach($this->getConfig('include') as $table=>$columnArray) {
            $sql = rex_sql::factory();
            $sql->setQuery('SELECT COUNT(*) AS count FROM `'.$table.'`');
            $count = intval($sql->getValue('count'));
            $step_width = 100;

            for($i = 0; $i < $count; $i += $step_width) {
                foreach($columnArray as $column) {
                    #$js_output .= 'index("col",new Array("'.$table.'","'.$column.'"));';
                    $js_output .= 'indexArray.push(new Array("col",new Array("'.$table.'","'.$column.'",'.$i.','.$step_width.')));';
                    $globalcount++;
                }
            }
        }
    }

    if(!empty($this->getConfig('indexmediapool')) AND intval($this->getConfig('indexmediapool'))) {
        $mediaSQL = rex_sql::factory();
        $mediaSQL->setTable(rex::getTable('media'));
        if($mediaSQL->select('id, category_id, filename')) {
            foreach($mediaSQL->getArray() as $file) {
                if(!empty($this->getConfig('fileextensions'))) {
                    // extract file-extension
                    $filenameArray = explode('.', $file['filename']);
                    $fileext = $filenameArray[count($filenameArray) - 1];

                    // check file-extension
                    if(!in_array($fileext, $this->getConfig('fileextensions'))){ continue; }
                }
                #$js_output .= 'index("mediapool",new Array("'.urlencode($file['filename']).'","'.urlencode($file['file_id']).'","'.urlencode($file['category_id']).'"));';
                $js_output .= 'indexArray.push(new Array("mediapool",new Array("'.urlencode('media/' .$file['filename']).'","'.urlencode($file['id']).'","'.urlencode($file['category_id']).'")));';
                $globalcount++;
            }
        }
    }

    if(!empty($this->getConfig('indexfolders')) AND is_array($this->getConfig('indexfolders'))) {
        foreach($this->getConfig('indexfolders') as $dir) {
            foreach(search_it_getFiles($dir, !empty($this->getConfig('fileextensions')) ? $this->getConfig('fileextensions') :[], true) as $filename) {
                if(!empty($this->getConfig('fileextensions'))) {
                  // extract file-extension
                  $filenameArray = explode('.', $filename);
                  $fileext = $filenameArray[count($filenameArray) - 1];

                  // check file-extension
                  if(!in_array($fileext, $this->getConfig('fileextensions'))){ continue; }
                }

                #$js_output .= 'index("file","'.($filename).'");';
                $js_output .= 'indexArray.push(new Array("file","'.($filename).'"));';
                $globalcount++;
            }
        }
    }
?>
    <script type="text/javascript">
    // <![CDATA[
        var globalcount = 0;
        var indexArray = new Array();
        var quotient = 0;
        var maxProgressbarWidth = jQuery('#search_it_generate_inprogress').width();
        var startTime = new Date();
        var h,m,s,duration,average,timeleft;

        <?php echo $js_output; ?>

        function index(type,data){
            var url;
            if(type === 'art') {
                url = 'index.php?page=search_it&ajax=generate&do=incremental&type=art&id=' + data;
            } else if(type === 'url') {
                url = 'index.php?page=search_it&ajax=generate&do=incremental&type=url&url_hash=' + data;
            } else if(type === 'col') {
                url = 'index.php?page=search_it&ajax=generate&do=incremental&type=col&t=' + data[0] + '&c=' + data[1] + '&s=' + data[2] + '&w=' + data[3];
            } else if(type === 'file') {
                url = 'index.php?page=search_it&ajax=generate&do=incremental&type=file&name=' + data;
            } else if(type === 'mediapool') {
                url = 'index.php?page=search_it&ajax=generate&do=incremental&type=mediapool&name=' + data[0] + '&file_id=' + data[1] + '&category_id=' + data[2];
            }

            jQuery.get(url,{},function(data){
                jQuery('#search_it_generate_log').prepend(data);
                globalcount++;

                quotient = globalcount / <?php echo $globalcount; ?>;

                currentDuration = (new Date()) - startTime;
                durationSeconds = Math.floor(currentDuration / 1000);
                h = Math.floor(durationSeconds / 3600);
                m = Math.floor((durationSeconds - (h * 3600)) / 60);
                s = (durationSeconds - h * 3600 - m * 60) % 60;
                duration = ((''+h).length === 1 ? '0' : '') + h + ':' + ((''+m).length === 1 ? '0' : '') + m + ':' + ((''+s).length === 1 ? '0' : '') + s;

                average = Math.floor(currentDuration / globalcount * (<?php echo $globalcount; ?> - globalcount) / 1000);
                h = Math.floor(average / 3600);
                m = Math.floor((average - (h * 3600)) / 60);
                s = (average - h * 3600 - m * 60) % 60;
                timeleft = ((''+h).length === 1 ? '0' : '') + h + ':' + ((''+m).length === 1 ? '0' : '') + m + ':' + ((''+s).length === 1 ? '0' : '') + s;

                jQuery('#search_it_generate_progressbar')
                    .css('background-position',(Math.floor(quotient * maxProgressbarWidth) - 5000) + 'px 0')
                    .html(  globalcount + '/' + <?php echo $globalcount; ?> +
                          ' <span class="duration"><?php echo $this->i18n('search_it_generate_duration'); ?>' + duration + '<'+'/span>' +
                          ' <span class="timeleft"><?php echo $this->i18n('search_it_generate_timeleft'); ?>' + timeleft + '<'+'/span>' +
                          ' <span class="percentage">' + Math.floor(quotient * 100) + '%<'+'/span>');

                if(globalcount === <?php echo $globalcount; ?>){
                    jQuery('#search_it_generate_inprogress').hide();
                    jQuery('#search_it_generate_done').show();
                    jQuery('#search_it_generate_log').addClass('index-done');
                } else {
                    index(indexArray[globalcount][0], indexArray[globalcount][1]);
                }
            });
        }

        <?php if($globalcount > 0) { ?>
                if(confirm('<?php echo $this->i18n('search_it_generate_incremental_confirm'); ?>')){
                    var del = new Image();
                    del.src = 'index.php?page=search_it&ajax=deleteindex';

                    index(indexArray[0][0], indexArray[0][1]);
                } else {
                    jQuery('#search_it_generate_inprogress').hide();
                    jQuery('#search_it_generate_cancel').show();
                    jQuery('#search_it_generate_log').addClass('index-done');
                }
        <?php } else { ?>
                jQuery('#search_it_generate_inprogress').hide();
                jQuery('#search_it_generate_done').show();
        <?php } ?>

        jQuery('#search_it_generate_header').after(
            jQuery('<div>')
            .attr('id','search_it_generate_progressbar')
            //.attr('class','alert alert-warning')
            .html('0/0 <span class="duration"><?php echo $this->i18n('search_it_generate_duration'); ?>00:00:00<'+'/span> <span class="timeleft"><?php echo $this->i18n('search_it_generate_timeleft'); ?>00:00:00<'+'/span> <span class="percentage">0%<'+'/span>')
        );
    // ]]>
    </script>
<?php

} else {
    if(!empty(rex_get('do'))){
        switch( rex_get('do') ){
            case 'done':
                echo rex_view::success($this->i18n('search_it_generate_done'));
                break;

            case 'full':
                $index = new search_it();
                $global_return = $index->generateIndex();
                if ( $global_return < 4 ) {
                    echo rex_view::success($this->i18n('search_it_generate_done'));
                } else {
                    echo rex_view::warning(rex_i18n::rawMsg('search_it_generate_error'));
                }
                break;

            case 'deletecache':
                $index = new search_it();
                $index->deleteCache();
                echo rex_view::success($this->i18n('search_it_generate_cache_deleted'));
                break;

            case 'deletekeywords':
                $index = new search_it();
                $index->deleteKeywords();
                echo rex_view::success($this->i18n('search_it_generate_keywords_deleted'));
                break;
        }
    }

    $content = '<p>'.$this->i18n('search_it_generate_full_text').'</p>';
    $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/generate&amp;do=full" class="rex-button">' . $this->i18n('search_it_generate_full') . '</a></p><br />';
    $content .= '<p>'.$this->i18n('search_it_generate_incremental_text').'</p>';
    $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/generate&amp;do=incremental" class="rex-button">' . $this->i18n('search_it_generate_incremental') . '</a></p><br />';
    $content .= '<p>'.$this->i18n('search_it_generate_delete_cache_text').'</p>';
    $content .= '<p><a class="btn btn-primary" href="index.php?page=search_it/generate&amp;do=deletecache" class="rex-button">' . $this->i18n('search_it_generate_delete_cache') . '</a></p><br />';
    $content .= '<p>'.$this->i18n('search_it_generate_delete_keywords_text').'</p>';
    $content .= '<p><a onclick="return confirm(\''.$this->i18n('search_it_generate_delete_keywords_confirm').'\');" class="btn btn-primary" href="index.php?page=search_it/generate&amp;do=deletekeywords" class="rex-button">' . $this->i18n('search_it_generate_delete_keywords') . '</a><br /><br /></p>';

    $content = rex_extension::registerPoint(new rex_extension_point('SEARCH_IT_PAGE_MAINTENANCE', $content));

    $fragment = new rex_fragment();
    $fragment->setVar('title', $this->i18n('search_it_generate_actions_title'), false);
    $fragment->setVar('body', $content , false);
    echo $fragment->parse('core/page/section.php');
}
