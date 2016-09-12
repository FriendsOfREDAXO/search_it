(function (jQuery) {
        jQuery(document).ready(function () {


            // ajax request for sample-text
            jQuery('#search_it_highlight').change(function(){
               jQuery.get('index.php?page=search_it&ajax=sample&type=' + jQuery('#search_it_highlight').val(), {}, function (data) {
                    jQuery('#search_it_sample').html(data);
                });
            });


            // open datebase tables
            var active_tables = jQuery();
            jQuery('.include_checkboxes').each(function (i, elem) {
                if (jQuery('.checkbox input:checked', elem).length > 0) {
                    active_tables = active_tables.add(jQuery('header',elem));
                }
            });
            active_tables.click();


            // directory-selection
            function getElementByValue(elements, value) {
                var returnElem = false;
                jQuery.each(elements, function (i, elem) {
                    if (elem.value == value) {
                        returnElem = elem;
                        return false;
                    }
                });

                return returnElem;
            }

            function setDirs() {
                var depth = 0, dirs = new Array(), found, indexdirs;
                while (document.getElementById('subdirs_' + depth)) {
                    jQuery.each(jQuery('#subdirs_' + depth + ' option'), function (i, elem) {
                        if (elem.selected) {
                            dirs.push(elem.value);
                        }
                    });

                    depth++;
                }

                indexdirs = new Array();
                for (var k = 0; k < dirs.length; k++) {
                    found = false;
                    for (var i = 0; i < dirs.length; i++) {
                        //if(dirs[k].substring(0,dirs[k].lastIndexOf('/')) == dirs[i])
                        if ((dirs[i].indexOf(dirs[k]) >= 0) && (i != k)) {
                            found = true;
                            //dirs.splice(i,1);
                            //break;
                        }
                    }

                    if (!found) {
                        indexdirs.push(dirs[k]);
                    }
                }

                jQuery('#search_it_settings_folders').empty();

                jQuery.each(indexdirs, function (i, elem) {
                    jQuery('#search_it_settings_folders')
                        .append(
                            jQuery('<option>')
                                .attr('value', elem)
                                .text(elem)
                        );
                });
            }

            function traverseSubdirs(depth, options) {
                var found, empty, activeOptions = new Array(), elem;

                for (var i = 0; i < options.length; i++) {
                    if ((elem = getElementByValue(jQuery('#subdirs_' + (depth - 1) + ' option'), options[i])) && elem.selected) {
                        activeOptions.push(options[i]);
                    }
                }

                while (document.getElementById('subdirs_' + depth)) {
                    empty = true;
                    jQuery.each(jQuery('#subdirs_' + depth + ' option'), function (i, elem) {
                        found = false;
                        for (var k = 0; k < activeOptions.length; k++) {
                            found = found || (elem.value.indexOf(activeOptions[k]) >= 0);
                        }

                        if (!found) {
                            jQuery(elem).remove();
                        } else {
                            empty = false;
                        }
                    });

                    if (empty) {
                        jQuery('#subdirs_' + depth).remove();
                        jQuery('#subdirselectlabel_' + depth).remove();
                    }

                    depth++;
                }
            }

            function search_it_serialize(a) {
                var anew = new Array();
                for (var i = 0; i < a.length; i++) {
                    anew.push('"' + (a[i].replace(/"/g, '\\"')) + '"');
                }
                return '[' + anew.join(',') + ']';
            }

            function createSubdirSection(depth, autoselect) {
                var parent, options, startdirstring = '', startdirs = new Array();
                if (depth == 0) {
                    parent = '#search_it_settings_folders';
                } else {
                    parent = '#subdirs_' + (depth - 1);
                    jQuery.each(jQuery('#subdirs_' + (depth - 1) + ' option'), function (i, elem) {
                        if (elem.selected) {
                            startdirs.push(elem.value);
                        }
                    });
                }

                if (depth > 0 && !startdirs.length) {
                    var currentDepth = depth;
                    while (document.getElementById('subdirs_' + currentDepth)) {
                        jQuery('#subdirs_' + (currentDepth)).remove();
                        jQuery('#subdirselectlabel_' + (currentDepth++)).remove();
                    }

                    jQuery('#search_it_files .loading').remove();

                    while (document.getElementById('subdirs_' + (--depth))) {
                        jQuery('#subdirs_' + (depth--)).removeAttr('disabled');
                    }

                    return false;
                } else {
                    jQuery.get('index.php?page=search_it&ajax=getdirs', {'startdirs': search_it_serialize(startdirs)}, function (options) {
                        if (!document.getElementById('subdirs_' + depth) && options.length > 0) {
                            jQuery(parent)
                                .after(
                                    jQuery('<select>')
                                        .attr('id', 'subdirs_' + depth)
                                        .attr('class', 'rex-form-text form-control subdirselect')
                                        .attr('multiple', 'multiple')
                                        .attr('size', '10')
                                        .change(function () {
                                            createSubdirSection(depth + 1);
                                            traverseSubdirs(depth + 1, options);
                                            setDirs();
                                        })
                                )
                                .after(
                                    jQuery('<label>')
                                        //.text(('<?php echo $this->i18n('search_it_settings_folders_dirselect_label'); ?>').replace(/%DEPTH%/, depth))
                                        .text(('Unterordner der Tiefe %DEPTH% auswählen').replace(/%DEPTH%/, depth))
                                        .attr('for', 'subdirs_' + depth)
                                        .attr('class', 'subdirselectlabel')
                                        .attr('id', 'subdirselectlabel_' + depth)
                                );

                            if (autoselect) {
                                jQuery('#subdirs_' + depth).attr('disabled', 'disabled');
                            }
                        }

                        for (var i = 0; i < options.length; i++) {
                            if (!getElementByValue(jQuery('#subdirs_' + depth + ' option'), options[i])) {
                                if (autoselect) {
                                    var found = false;
                                    jQuery('#search_it_settings_folders option').each(function (j, elem) {
                                        found = found || (elem.value.indexOf(options[i]) >= 0);

                                        if (found) {
                                            return false;
                                        }
                                    });

                                    if (found) {
                                        jQuery('#subdirs_' + depth)
                                            .append(
                                                jQuery('<option>')
                                                    .attr('value', options[i])
                                                    .attr('selected', 'selected')
                                                    .text(options[i])
                                            );
                                    } else {
                                        jQuery('#subdirs_' + depth)
                                            .append(
                                                jQuery('<option>')
                                                    .attr('value', options[i])
                                                    .text(options[i])
                                            );
                                    }
                                } else {
                                    jQuery('#subdirs_' + depth)
                                        .append(
                                            jQuery('<option>')
                                                .attr('value', options[i])
                                                .text(options[i])
                                        );
                                }
                            }
                        }

                        if (autoselect) {
                            var maxDepth = 0, splitted, current, count;
                            jQuery('#search_it_settings_folders option').each(function (i, elem) {
                                if ((elem.id != 'search_it_optiondummy') && ((count = elem.value.split('/').length - 2) > maxDepth)) {
                                    maxDepth = count;
                                }
                            });

                            if (maxDepth >= depth) {
                                createSubdirSection(depth + 1, true);
                            } else {
                                jQuery('#search_it_files .loading').remove();

                                depth = 0;
                                while (document.getElementById('subdirs_' + depth))
                                    jQuery('#subdirs_' + (depth++)).removeAttr('disabled');

                                depth--;

                            }
                        }
                    }, 'json');

                    return true;
                }
            }

            var options;
            // beautifying the indexed folders selectbox and selecting the options in the subdir-selectboxes
            jQuery('#search_it_settings_folders').attr('disabled', 'disabled');
            jQuery.each(options = jQuery('#search_it_settings_folders option'), function (i, elem) {
                var splitted, current, depth = 0;

                elem.selected = false;

                if (options.length - 1 == i) {
                    createSubdirSection(depth, true);
                }
            });

            // selected folders form the value
            jQuery('#search_it_settings_form').submit(function () {
                jQuery('#search_it_settings_folders').removeAttr('disabled');
                jQuery.each(jQuery('#search_it_settings_folders option'), function (i, elem) {
                    if (elem.value != '') {
                        elem.selected = true;
                    }
                });
                return true;
            });
        });
})(jQuery);
