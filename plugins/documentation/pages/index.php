<?php

//echo rex_view::title(rex_i18n::msg('search_it_documentation_title'));

// refresh
// Umbauen auf zipball... weil nur ein request
// unauthorisiert sind maximal 60 requests die stunde erlaubt, daher dieser weg hier nicht sinnvoll
/*if (rex_request("search_it_documentation_func","string") == "refresh") {
    try {
        $files_socket = rex_socket::factoryURL('https://api.github.com/repos/tyrant88/search_it/git/trees/master?recursive=1');
        $response = $files_socket->doGet();
        if($response->isOk()) {
            $files = json_decode($response->getBody(), true);
            $file_path = rex_path::plugin('search_it','documentation','docs/');
            if (isset($files["tree"]) && is_array($files["tree"])) {
                foreach ($files["tree"] as $file) {
                    if (substr($file["path"],0,6) == "de_de/" && $file["type"] == "blob" && $file["url"] != "") {
                        $file_socket = rex_socket::factoryURL($file["url"]);
                        $response = $file_socket->doGet();
                        if($response->isOk()) {
                            $file_info = json_decode($response->getBody(), true);
                            rex_file::put($file_path.$file["path"], base64_decode($file_info["content"]));
                            echo "*";
                        }
                    }
                }
            }
        }
    } catch(rex_socket_exception $e) {
    }
}*/

$langpath = rex::getProperty('lang');
$path = rex_path::plugin('search_it','documentation','docs/'.$langpath.'/');

$files = [];
foreach(scandir($path) as $i_file) {
    if ($i_file != "." && $i_file != "..") {
        $files[$i_file] = $i_file;
    }
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
$fragment->setVar('title', rex_i18n::msg('search_it_documentation_navigation').' [ <a href="https://github.com/tyrant88/search_it/tree/master/'.$lang.'/main_navi.md">main_navi.md</a> ] <!-- <span><a href="index.php?page=yform/docs&yform_docs_func=refresh">Refresh</a></span> -->', false);
$fragment->setVar('body', $navi, false);
$navi = $fragment->parse('core/page/section.php');


$fragment = new rex_fragment();
$fragment->setVar('title', rex_i18n::msg('search_it_documentation_content').' [ <a href="https://github.com/tyrant88/search_it/tree/master/'.$lang.'/'.basename($file).'">'.basename($file).'</a> ]', false);
$fragment->setVar('body', $content, false);
$content = $fragment->parse('core/page/section.php');


echo '<section class="search_it_documentation">
    <div class="row">
    <div class="col-md-4 search_it_documentation-navi">'.$navi.'</div>
    <div class="col-md-8 search_it_documentation-content">'.$content.'</div>
    </div>
</section>';