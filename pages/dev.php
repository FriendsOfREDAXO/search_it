<?php

if (rex_get('write', 'int', 0)) {
    echo rex_view::success("Module und Templates wurden ins Addon geschrieben. Das Addon ist jetzt bereit für den Installer / GitHub.");


    $modules = rex_sql::factory()->setDebug(0)->getArray("SELECT * FROM rex_module WHERE `key` LIKE '%search_it_%'");

    foreach ($modules as $module) {
        rex_file::put(rex_path::addon("across", "module/".$module['key'].".json"), json_encode($module));
    }

    $templates = rex_sql::factory()->setDebug(0)->getArray("SELECT * FROM rex_template WHERE `key` LIKE '%search_it_%'");


    foreach ($templates as $template) {
        rex_file::put(rex_path::addon("across", "template/".$template['key'].".json"), json_encode($template));
    }
}




$button = '<a class="btn btn-primary" href="'.rex_url::currentBackendPage(['write'=>'1']).'">ins Dateisystem schreiben</a>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', "Templates und Module schreiben", false);
$fragment->setVar('body', $button, false);
echo $fragment->parse('core/page/section.php');
