<form class="search_it-form"
    action="<?php echo rex_getUrl($this->article_id, rex_clang::getCurrentId()); ?>"
    method="get">
    <fieldset>
        <legend><?= $this->legend ?>
        </legend>
        <div class="search_it-flex">
            <input type="hidden" name="article_id"
                value="<?= $this->article_id; ?>" />
            <input type="hidden" name="clang"
                value="<?php echo rex_clang::getCurrentId(); ?>" />
            <label for="search"><?= $this->label ?></label>
            <input type="text" name="search" value="<?php if (!empty(rex_request('search', 'string'))) {
    echo rex_escape(rex_request('search', 'string'));
} ?>" placeholder="<?= $this->placeholder ?>" />
            <input class="search_it-button" type="submit"
                value="<?= $this->button ?>" />
        </div>
    </fieldset>
</form>