<?php

if (rex_post('config-submit', 'boolean')) {

    $posted_config = rex_post('search_config', [
        ['logicalmode', 'string'],
        ['textmode', 'string'],
        ['similarwordsmode', 'string'],
        ['similarwords_permanent', 'bool'],
        ['searchmode', 'string'],

        ['indexmode', 'string'],
        ['indexoffline', 'bool'],
        ['automaticindex', 'bool'],
        ['ep_outputfilter', 'bool'],

    ]);


    // do it
    $this->setConfig($posted_config);

    //tell it
    echo rex_view::success($this->i18n('search_it_settings_saved'));


    /*    echo '<pre>';
    var_dump(rex_post('search_config'));
    echo "\n";
    var_dump( $this->getConfig());
    echo '</pre>';*/

    foreach( array_keys(array_merge(array_diff_assoc($posted_config,$this->getConfig(), array_diff_assoc($this->getConfig(),$posted_config)))) as $changed) {
        if(in_array($changed, array(
            'indexmode',
            'indexoffline',
            'automaticindex',
            'blacklist',
            'exclude_article_ids',
            'exclude_category_ids',
            'include',
            'fileextensions',
            'indexmediapool',
            'dirdepth',
            'indexfolders',
            'ep_outputfilter'
        ))) {
                echo rex_view::warning($this->i18n('search_it_settings_saved_warning')); break;
            }
    }
}


?>
<script type="text/javascript">
// <![CDATA[
// width of the formular
(function($){
  $(document).ready(function(){
    var mainWidth = $('#search_it-form').width();

    // set loading image for filesearch-config
    $('#search_it_files legend').append(
      $('<span>')
      .attr('class','loading')
    );



    // ajax request for sample-text
    $('#search_it_highlight')
    .change(function(){
      $.get('index.php?page=search_it&ajax=sample&type='+$('#search_it_highlight').attr('value'),{},function(data){
        $('#search_it_sample').html(data);
      });
    });


    // categorize datebase tables
    var current_table = '';
    $('#search_it_include .checkbox').each(function(i, elem){
      var table = $('input', elem).attr('value').split(/`.`/)[0];

      $('label', elem).text($('input', elem).attr('value').split(/`.`/)[1]);

      if(current_table != table){
        $(elem).before(
          $('<div>').addClass('checkbox-heading rex-form-row').text(table).click(function(){
            var $next = $(this).next();
            var $elements = $next;
            while($next.hasClass('checkbox')){
              //$next.show();
              $elements = $elements.add($next);
              $next = $next.next();
            }

            $elements.toggle();
          })
        );

        current_table = table;
      }
    });

    var active_tables = $();
    $('#search_it_include .checkbox input:checked').each(function(i, elem){
      var $prev = $(this).closest('.checkbox').prev();
      while($prev.hasClass('checkbox')) {
          $prev = $prev.prev();
      }

      active_tables = active_tables.add($prev);
    });

    active_tables.click();


    // directory-selection
    function getElementByValue(elements, value) {
      var returnElem = false;
      $.each(elements, function(i, elem){
        if(elem.value == value){
          returnElem = elem;
          return false;
        }
      });

      return returnElem;
    }

    function setDirs(){
      var depth = 0,dirs = new Array(),found,indexdirs;
      while(document.getElementById('subdirs_'+depth)){
        $.each($('#subdirs_'+depth+' option'), function(i, elem){
          if(elem.selected) {
              dirs.push(elem.value);
          }
        });

        depth++;
      }

      indexdirs = new Array();
      for(var k=0; k < dirs.length; k++){
        found = false;
        for(var i=0; i < dirs.length; i++){
          //if(dirs[k].substring(0,dirs[k].lastIndexOf('/')) == dirs[i])
          if((dirs[i].indexOf(dirs[k]) >= 0) && (i != k)){
            found = true;
            //dirs.splice(i,1);
            //break;
          }
        }

        if(!found) {
            indexdirs.push(dirs[k]);
        }
      }

      $('#search_it_settings_folders').empty();

      $.each(indexdirs, function(i, elem){
        $('#search_it_settings_folders')
        .append(
          $('<option>')
          .attr('value', elem)
          .text(elem)
        );
      });
    }

    function traverseSubdirs(depth, options){
      var found,empty,activeOptions = new Array(),elem;

      for(var i = 0; i < options.length; i++){
        if((elem = getElementByValue($('#subdirs_'+(depth-1)+' option'), options[i])) && elem.selected) {
            activeOptions.push(options[i]);
        }
      }

      while(document.getElementById('subdirs_'+depth)){
        empty = true;
        $.each($('#subdirs_'+depth+' option'), function(i, elem){
          found = false;
          for(var k = 0; k < activeOptions.length; k++){
            found = found || (elem.value.indexOf(activeOptions[k]) >= 0);
          }

          if(!found) {
              $(elem).remove();
          } else {
              empty = false;
          }
        });

        if(empty){
          $('#subdirs_'+depth).remove();
          $('#subdirselectlabel_'+depth).remove();
        }

        depth++;
      }
    }

    function search_it_serialize(a){
      var anew = new Array();
      for(var i = 0; i < a.length; i++) {
          anew.push('"' + (a[i].replace(/"/g, '\\"')) + '"');
      }
      return '[' + anew.join(',') + ']';
    }

    function createSubdirSection(depth,autoselect){
      var parent,options,startdirstring = '',startdirs = new Array();
      if(depth == 0){
        parent = '#search_it_settings_folders';
      } else {
        parent = '#subdirs_'+(depth-1);
        $.each($('#subdirs_'+(depth-1)+' option'), function(i, elem){
          if(elem.selected){
            startdirs.push(elem.value);
          }
        });
      }

      if(depth > 0 && !startdirs.length){
        var currentDepth = depth;
        while(document.getElementById('subdirs_'+currentDepth)){
          $('#subdirs_'+(currentDepth)).remove();
          $('#subdirselectlabel_'+(currentDepth++)).remove();
        }

        $('#search_it_files .loading').remove();

        while(document.getElementById('subdirs_'+(--depth))) {
            $('#subdirs_' + (depth--)).removeAttr('disabled');
        }

        return false;
      } else {
        $.post('index.php?page=search_it&ajax=getdirs', {'startdirs':search_it_serialize(startdirs)}, function(options){
          if(!document.getElementById('subdirs_'+depth) && options.length > 0){
            $(parent)
            .after(
              $('<select>')
              .attr('id','subdirs_'+depth)
              .attr('class','rex-form-text subdirselect')
              .attr('multiple','multiple')
              .attr('size','10')
              .change(function(){
                createSubdirSection(depth+1);
                traverseSubdirs(depth+1, options);
                setDirs();
              })
            )
            .after(
              $('<label>')
              .text(('<?php echo $this->i18n('search_it_settings_folders_dirselect_label'); ?>').replace(/%DEPTH%/, depth))
              .attr('for','subdirs_'+depth)
              .attr('class','subdirselectlabel')
              .attr('id','subdirselectlabel_'+depth)
            );

            if(autoselect) {
                $('#subdirs_' + depth).attr('disabled', 'disabled');
            }
          }

          for(var i = 0; i < options.length; i++){
            if(!getElementByValue($('#subdirs_'+depth+' option'), options[i])){
              if(autoselect){
                var found = false;
                $('#search_it_settings_folders option').each(function(j, elem){
                  found = found || (elem.value.indexOf(options[i]) >= 0);

                  if(found) {
                      return false;
                  }
                });

                if(found){
                  $('#subdirs_'+depth)
                  .append(
                    $('<option>')
                    .attr('value', options[i])
                    .attr('selected', 'selected')
                    .text(options[i])
                  );
                } else {
                  $('#subdirs_'+depth)
                  .append(
                    $('<option>')
                    .attr('value', options[i])
                    .text(options[i])
                  );
                }
              } else {
                $('#subdirs_'+depth)
                .append(
                  $('<option>')
                  .attr('value', options[i])
                  .text(options[i])
                );
              }
            }
          }

          if(autoselect){
            var maxDepth = 0,splitted,current,count;
            $('#search_it_settings_folders option').each(function(i, elem){
              if((elem.id != 'search_it_optiondummy') && ((count = elem.value.split('/').length-2) > maxDepth)) {
                  maxDepth = count;
              }
            });

            if(maxDepth >= depth){
              createSubdirSection(depth+1,true);
            } else {
              $('#search_it_files .loading').remove();

              depth = 0;
              while(document.getElementById('subdirs_'+depth))
                $('#subdirs_'+(depth++)).removeAttr('disabled');

              depth--;

              // adapt width of legend
              var legend = $('#search_it_files legend');
              legend.css('padding-right', (mainWidth - legend.attr('offsetWidth') + parseInt(legend.css('padding-right').replace(/[^0-9]+/,''))) + 'px');
            }
          }
        }, 'json');

      return true;
      }
    }

    var options;
    // beautifying the indexed folders selectbox and selecting the options in the subdir-selectboxes
    $('#search_it_settings_folders').attr('disabled','disabled');
    $.each(options = $('#search_it_settings_folders option'), function(i, elem){
      var splitted,current,depth=0;

      elem.selected = false;

      if(options.length - 1 == i) {
          createSubdirSection(depth, true);
      }
    });

    $('#search_it_settings_form').submit(function(){
      $('#search_it_settings_folders').removeAttr('disabled');
      $.each($('#search_it_settings_folders option'), function(i, elem){
        if(elem.value != '')
          elem.selected = true;
      });

      return true;
    });
  });
}(jQuery));

// ]]>
</script>
<?php

$content = '';
$formElements = [];


$content[] = search_it_getSettingsFormSection(
    'search_it_modi',
    $this->i18n('search_it_settings_modi_header'),
    array(
        array(
            'type' => 'select',
            'id' => 'search_it_logicalmode',
            'name' => 'search_config[logicalmode]',
            'label' => $this->i18n('search_it_settings_logicalmode'),
            'options' => array(
                array(
                    'value' => 'and',
                    'selected' => $this->getConfig('logicalmode') == 'and',
                    'name' => $this->i18n('search_it_settings_logicalmode_and')
                ),
                array(
                    'value' => 'or',
                    'selected' => $this->getConfig('logicalmode') == 'or',
                    'name' => $this->i18n('search_it_settings_logicalmode_or')
                )
            )
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_textmode',
            'name' => 'search_config[textmode]',
            'label' => $this->i18n('search_it_settings_textmode'),
            'options' => array(
                array(
                    'value' => 'plain',
                    'selected' => $this->getConfig('textmode') == 'plain',
                    'name' => $this->i18n('search_it_settings_textmode_plain')
                ),
                array(
                    'value' => 'html',
                    'selected' => $this->getConfig('textmode') == 'html',
                    'name' => $this->i18n('search_it_settings_textmode_html')
                ),
                array(
                    'value' => 'both',
                    'selected' => $this->getConfig('textmode') == 'both',
                    'name' => $this->i18n('search_it_settings_textmode_both')
                )
            )
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_similarwords_mode',
            'name' => 'search_config[similarwordsmode]',
            'label' => $this->i18n('search_it_settings_similarwords_label'),
            'options' => array(
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_NONE,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_NONE,
                    'name' => $this->i18n('search_it_settings_similarwords_none')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_SOUNDEX,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_SOUNDEX,
                    'name' => $this->i18n('search_it_settings_similarwords_soundex')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_METAPHONE,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_METAPHONE,
                    'name' => $this->i18n('search_it_settings_similarwords_metaphone')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_COLOGNEPHONE,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_COLOGNEPHONE,
                    'name' => $this->i18n('search_it_settings_similarwords_cologne')
                ),
                array(
                    'value' => SEARCH_IT_SIMILARWORDS_ALL,
                    'selected' => $this->getConfig('similarwordsmode') == SEARCH_IT_SIMILARWORDS_ALL,
                    'name' => $this->i18n('search_it_settings_similarwords_all')
                )
            )
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_similarwords_permanent',
            'name' => 'search_config[similarwords_permanent]',
            'label' => $this->i18n('search_it_settings_similarwords_permanent'),
            'value' => '1',
            'checked' => !empty($this->getConfig('similarwords_permanent'))
        ),
        array(
            'type' => 'select',
            'id' => 'search_it_searchmode',
            'name' => 'search_config[searchmode]',
            'label' => $this->i18n('search_it_settings_searchmode'),
            'options' => array(
                array(
                    'value' => 'like',
                    'selected' => $this->getConfig('searchmode') == 'like',
                    'name' => $this->i18n('search_it_settings_searchmode_like')
                ),
                array(
                    'value' => 'match',
                    'selected' => $this->getConfig('searchmode') == 'match',
                    'name' => $this->i18n('search_it_settings_searchmode_match')
                )
            )
        )
    )
);



$content[] = search_it_getSettingsFormSection(
    'search_it_index',
    $this->i18n('search_it_settings_title_indexmode'),
    array(
        array(
            'type' => 'select',
            'id' => 'search_it_settings_indexmode',
            'name' => 'search_config[indexmode]',
            'label' => $this->i18n('search_it_settings_indexmode_label'),
            'options' => array(
                array(
                    'value' => '0',
                    'name' => $this->i18n('search_it_settings_indexmode_viahttp'),
                    'selected' => $this->getConfig('indexmode') == '0',
                ),
                array(
                    'value' => '1',
                    'name' => $this->i18n('search_it_settings_indexmode_viacache'),
                    'selected' => $this->getConfig('indexmode') == '1',
                ),
                array(
                    'value' => '2',
                    'name' => $this->i18n('search_it_settings_indexmode_viacachetpl'),
                    'selected' => $this->getConfig('indexmode') == '2',
                )
            )
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_indexoffline',
            'name' => 'search_config[indexoffline]',
            'label' => $this->i18n('search_it_settings_indexoffline'),
            'value' => '1',
            'checked' => !empty($this->getConfig('indexoffline'))
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_automaticindex',
            'name' => 'search_config[automaticindex]',
            'label' => $this->i18n('search_it_settings_automaticindex_label'),
            'value' => '1',
            'checked' => !empty($this->getConfig('automaticindex'))
        ),
        array(
            'type' => 'checkbox',
            'id' => 'search_it_ep_outputfilter',
            'name' => 'search_config[ep_outputfilter]',
            'label' => $this->i18n('search_it_settings_ep_outputfilter_label'),
            'value' => '1',
            'checked' => !empty($this->getConfig('ep_outputfilter'))
        )
    )
);


$fragment = new rex_fragment();
$fragment->setVar('content', $content, false);
$content = $fragment->parse('core/page/grid.php');


$formElements = [];
$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="config-submit" value="1" ' . rex::getAccesskey($this->i18n('search_it_settings_submitbutton'), 'save') . '>' . $this->i18n('search_it_settings_submitbutton') . '</button>';
$formElements[] = $n;
$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');


$fragment = new rex_fragment();
$fragment->setVar('buttons', $buttons, false);
$content .= $fragment->parse('core/page/section.php');

echo '
<form id="search_it_settings_form" action="' . rex_url::currentBackendPage() . '" method="post">
' . $content . '
</form>';