<?php
/**
 * Search it Addon.
 * @author @tyrant88
 * @package search_it
 * @var rex_addon $this
 */
if ( is_dir($this->getPlugin('reindex')->getPath()) ) {
    rex_dir::delete($this->getPlugin('reindex')->getPath());
    //echo rex_view::warning($this->i18n('search_it_settings_plugin_deleted'));
}
if ( is_dir($this->getPlugin('search_highlighter')->getPath()) ) {
    rex_dir::delete($this->getPlugin('search_highlighter')->getPath());
    //echo rex_view::warning($this->i18n('search_it_settings_plugin_deleted'));
}

