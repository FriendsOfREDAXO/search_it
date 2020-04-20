<?php

if ($this->result['count'] > $this->limit) {
    $self = rex_article::get($article_id); ?>
<ul class="search_it-pagination">
    <?php
    for ($i = 0; ($i * $this->limit) < $this->result['count']; $i++) {
        if (($i * $limit) == $this->start) {
            ?>
    <li class="current"><?= ($i + 1) ?>
    </li>
    <?php
        } else {
            ?>
    <li>
        <a
            href="<?= $self->getUrl(array('search' => $this->request, 'start' => $i * $this->limit)) ?>"><?= ($i + 1) ?></a>
    </li>
    <?php
        }
    }
}
    ?>
</ul>