<?php

$langpath = rex::getProperty('lang');
$path = rex_path::plugin('search_it','documentation','docs/'.$langpath.'/');

$files = [];
foreach(rex_finder::factory($path)->filesOnly() as $file) {
    $files[$file->getFileName()] = $file->getFileName();
}


if ( rex_request("search_it_document_image","string") != "" && isset($files[rex_request("search_it_document_image","string")]) ) {
    ob_end_clean();
    $content = rex_file::get($path.basename(rex_request("search_it_document_image","string")));
    echo $content;
    exit;

}

$navi = rex_file::get($path.'main_navi.md');

$file = rex_request('search_it_document_file','string','search_it-intro.md');
if (!in_array($file, $files)) {
    $file = 'search_it_intro.md';
}
$content = rex_file::get($path.basename($file));
if ($content == "") {
    $content = '<p class="alert alert-warning">'.rex_i18n::rawMsg('search_it_documentation_filenotfound').'</p>';
}



if (class_exists("rex_markdown")) {

    $miu = rex_markdown::factory();
    $navi = $miu->parse($navi);
    $content = $miu->parse($content);

    foreach($files as $i_file) {

        $search = '#href="('.$i_file.')"#';
        $replace = 'href="index.php?page=search_it/documentation&search_it_document_file=$1"';
        $navi = preg_replace($search, $replace, $navi);
        $content = preg_replace($search, $replace, $content);

        // ![Alt-Text](bildname.png)
        // ![Ein Screenshot](screenshot.png)
        $search = '#\!\[(.*)\]\(('.$i_file.')\)#';
        $replace = '<img src="index.php?page=search_it/documentation&search_it_document_image=$2" alt="$1" style="width:100%"/>';
        $content = preg_replace($search, $replace, $content);

    }

}

$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('search_it_documentation_navigation'), false);
$fragment->setVar('body', $navi, false);
$navi = $fragment->parse('core/page/section.php');


$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('search_it_documentation_content'), false);
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');


echo '<section class="search_it_documentation">
    <div class="row">
    <div class="col-md-4 search_it_documentation-navi">'.$navi.'</div>
    <div class="col-md-8 search_it_documentation-content">'.$content.'</div>
    </div>
</section>';