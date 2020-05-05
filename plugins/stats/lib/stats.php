<?php

class search_it_stats{
    var $sql;

    function __construct(){
        $this->sql = rex_sql::factory();
        $this->flushSQL();
    }

    function flushSQL(){
        $this->sql->flushValues();
        $this->sql->setTable(rex::getTablePrefix(). 'search_it_stats_searchterms');
    }

    function insert($_searchterm, $_resultcount, $_time = false){
        $this->flushSQL();

        if (false === $_time) {
            $_time = date('Y-m-d H:i:s');
        }

        $this->sql->setValues(
            array(
                'term' => $_searchterm,
                'time' => $_time,
                'resultcount' => $_resultcount
            )
        );

        $this->sql->insert();
    }

    function truncate(){
        $this->sql->setQuery('TRUNCATE ' . rex::getTablePrefix(). 'search_it_stats_searchterms');
    }

    function getTopSearchterms($_count, $_getonly = 0){
        $this->flushSQL();

        if (empty($_getonly)){
            $query = 'SELECT term, COUNT(*) as count, 1 as success FROM `' . rex::getTablePrefix(). 'search_it_stats_searchterms' . '` WHERE resultcount > 0 GROUP BY term
                      UNION
                      SELECT term, COUNT(*) as count, 0 as success FROM `' . rex::getTablePrefix(). 'search_it_stats_searchterms' . '` WHERE resultcount <= 0 GROUP BY term';
        } else {
            $query = 'SELECT term, COUNT(*) as count, ' . ($_getonly == 1 ? 1 : 0) . ' as success FROM `' . rex::getTablePrefix().  'search_it_stats_searchterms' . '` WHERE resultcount ' . ($_getonly == 1 ? '>' : '<=') . ' 0 GROUP BY term';
            // getonly = 1: only successful searchterms
            // getonly = 2: only failed searchterms
        }
        $return = $this->sql->getArray(
            $query . '
      ORDER BY count DESC LIMIT ' . intval($_count)
        );

        if (empty($return)) {
            $return = [];
        }

        return $return;
    }

    function getSuccessCount(){
        $this->flushSQL();
        $this->sql->setWhere('resultcount > 0 LIMIT 1');
        $this->sql->select('COUNT(*) as success');
        $return = $this->sql->getArray();
        return intval($return[0]['success']);
    }

    function getMissCount(){
        $this->flushSQL();
        $this->sql->setWhere('resultcount = 0 LIMIT 1');
        $this->sql->select('COUNT(*) as miss');
        $return = $this->sql->getArray();
        return intval($return[0]['miss']);
    }

    function getCount($_count = 6){
        $this->flushSQL();
        $this->sql->setWhere('1 GROUP BY y, m ORDER BY y DESC, m DESC LIMIT ' . $_count);
        $this->sql->select('COUNT( * ) AS count, YEAR(`time`) AS y, MONTH(`time`) AS m');

        $tmp = [];
        foreach ($this->sql->getArray() as $month) {
            $tmp[intval($month['y']) . '-' . intval($month['m'])] = $month;
        }

        $return = [];
        $y = intval(date('Y'));
        for ($i = intval(date('n')) - 1, $k = 0; $k < $_count; $i = ($i + 11) % 12, $k++) {
            if (array_key_exists($y . '-' . ($i + 1), $tmp)) {
                $return[] = $tmp[$y . '-' . ($i + 1)];
            } else {
                $return[] = array(
                    'm' => $i + 1,
                    'y' => $y,
                    'count' => 0
                );
            }

            if ($i == 11)
                $y--;
        }

        return array_reverse($return);
    }

    function getSearchtermCount(){
        $this->flushSQL();
        $this->sql->select('COUNT(DISTINCT term) as count');
        $return = $this->sql->getArray();
        return intval($return[0]['count']);
    }

    function getTimestats($_term = '', $_count = 12){
        $this->flushSQL();
        if (!empty($_term))
            $where = 'term = ' . $this->sql->escape($_term) . '';
        else
            $where = '1';
        $this->sql->setWhere(sprintf('%s GROUP BY y, m ORDER BY y DESC, m DESC LIMIT %d', $where, $_count));
        $this->sql->select('COUNT( * ) AS count, YEAR(`time`) AS y, MONTH(`time`) AS m');

        $tmp = [];
        foreach ($this->sql->getArray() as $month) {
            $tmp[intval($month['y']) . '-' . intval($month['m'])] = $month;
        }

        $return = [];
        $y = intval(date('Y'));
        for ($i = intval(date('n')) - 1, $k = 0; $k < $_count; $i = ($i + 11) % 12, $k++) {
            if (array_key_exists($y . '-' . ($i + 1), $tmp)) {
                $return[] = $tmp[$y . '-' . ($i + 1)];
            } else {
                $return[] = array(
                    'm' => $i + 1,
                    'y' => $y,
                    'count' => 0
                );
            }

            if ($i == 11)
                $y--;
        }

        return array_reverse($return);
    }

    function getSearchCount(){
        $this->flushSQL();
        $this->sql->select('COUNT(*) as count');
        $return = $this->sql->getArray();
        return intval($return[0]['count']);
    }

    function createTestData(){
        $this->flushSQL();
        $str = '
Wir bieten Ihnen leckeres Essen, frische Steinofenpizza, verschiedene Pastavariationen und frische Salate für die ganze Familie,
Drinks in geselliger Runde oder an unserer Bar, einen gemütlichen Biergarten, Fremdenzimmer zu fairen Preisen und ab sofort auch
Pizza auf Bestellung (zum selber Abholen, kein Lieferservice). Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam
nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo
dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet,
consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.
At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor
sit amet. Werde kostenlos Mitglied in der party.de-Community! Hier treffen sich in erster Linie Feierleute, die gleichgesinnte kennenlernen
wollen, neue Freundschaften schliessen möchten und sich oftmals auch im realen Leben verabreden. Klick hier, um Mitglied zu werden.
Die Zwitter kommen: PC-Monitore im Breitbildformat sind angesagt, darunter immer öfter Modelle mit integriertem TV-Empfänger. Sie
sind die erste Wahl für alle, die nur Platz für ein Gerät haben oder am Schreibtisch auch mal fernsehen möchten. test hat vier Kombi-
geräte mit zwölf reinen PC-Bildschirmen verglichen. Probleme gabs nur im Detail.';

        $splitregex = '~\W+~ism';
        $splitregex .= 'u';

        $terms = array_unique(preg_split($splitregex, $str));

        for ($i = 0; $i <= 100000; $i++) {
            $this->insert($terms[rand(0, count($terms) - 1)], rand(0, 8), date('Y-m-d H:i:s', time() - (mt_rand(30000, 100000) * rand(0, 700))));
        }
    }
}
