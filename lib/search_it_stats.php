<?php

namespace FriendsOfRedaxo\SearchIt\Stats;

use rex;
use rex_sql;

class Statistics
{
    private rex_sql $sql;

    public function __construct()
    {
        $this->sql = rex_sql::factory();
        $this->flushSQL();
    }

    public function flushSQL(): void
    {
        $this->sql->flushValues();
        $this->sql->setTable(rex::getTablePrefix() . 'search_it_stats_searchterms');
    }

    public function insert(string $searchterm, int $resultcount, string|false $time = false): void
    {
        $this->flushSQL();

        if (false === $time) {
            $time = date('Y-m-d H:i:s');
        }

        $this->sql->setValues([
            'term' => $searchterm,
            'time' => $time,
            'resultcount' => $resultcount,
        ]);

        $this->sql->insert();
    }

    public function truncate(): void
    {
        $this->sql->setQuery('TRUNCATE ' . rex::getTablePrefix() . 'search_it_stats_searchterms');
    }

    public function getTopSearchterms(int $count, int $getonly = 0): array
    {
        $this->flushSQL();

        if (empty($getonly)) {
            $query = 'SELECT term, COUNT(*) as count, 1 as success FROM `' . rex::getTablePrefix() . 'search_it_stats_searchterms` WHERE resultcount > 0 GROUP BY term
                      UNION
                      SELECT term, COUNT(*) as count, 0 as success FROM `' . rex::getTablePrefix() . 'search_it_stats_searchterms` WHERE resultcount <= 0 GROUP BY term';
        } else {
            $query = 'SELECT term, COUNT(*) as count, ' . ($getonly == 1 ? 1 : 0) . ' as success FROM `' . rex::getTablePrefix() . 'search_it_stats_searchterms` WHERE resultcount ' . ($getonly == 1 ? '>' : '<=') . ' 0 GROUP BY term';
        }

        return $this->sql->getArray($query . ' ORDER BY count DESC LIMIT ' . $count) ?: [];
    }

    public function getSuccessCount(): int
    {
        $this->flushSQL();
        $this->sql->setWhere('resultcount > 0 LIMIT 1');
        $this->sql->select('COUNT(*) as success');
        $return = $this->sql->getArray();
        return (int) $return[0]['success'];
    }

    public function getMissCount(): int
    {
        $this->flushSQL();
        $this->sql->setWhere('resultcount = 0 LIMIT 1');
        $this->sql->select('COUNT(*) as miss');
        $return = $this->sql->getArray();
        return (int) $return[0]['miss'];
    }

    public function getCount(int $count = 6): array
    {
        $this->flushSQL();
        $this->sql->setWhere('1 GROUP BY y, m ORDER BY y DESC, m DESC LIMIT ' . $count);
        $this->sql->select('COUNT( * ) AS count, YEAR(`time`) AS y, MONTH(`time`) AS m');

        $tmp = [];
        foreach ($this->sql->getArray() as $month) {
            $tmp[(int) $month['y'] . '-' . (int) $month['m']] = $month;
        }

        $return = [];
        $y = (int) date('Y');
        for ($i = (int) date('n') - 1, $k = 0; $k < $count; $i = ($i + 11) % 12, $k++) {
            if (array_key_exists($y . '-' . ($i + 1), $tmp)) {
                $return[] = $tmp[$y . '-' . ($i + 1)];
            } else {
                $return[] = [
                    'm' => $i + 1,
                    'y' => $y,
                    'count' => 0,
                ];
            }

            if ($i == 11) {
                $y--;
            }
        }

        return array_reverse($return);
    }

    public function getSearchtermCount(): int
    {
        $this->flushSQL();
        $this->sql->select('COUNT(DISTINCT term) as count');
        $return = $this->sql->getArray();
        return (int) $return[0]['count'];
    }

    public function getTimestats(string $term = '', int $count = 12): array
    {
        $this->flushSQL();
        $where = !empty($term) ? 'term = ' . $this->sql->escape($term) : '1';
        $this->sql->setWhere(sprintf('%s GROUP BY y, m ORDER BY y DESC, m DESC LIMIT %d', $where, $count));
        $this->sql->select('COUNT( * ) AS count, YEAR(`time`) AS y, MONTH(`time`) AS m');

        $tmp = [];
        foreach ($this->sql->getArray() as $month) {
            $tmp[(int) $month['y'] . '-' . (int) $month['m']] = $month;
        }

        $return = [];
        $y = (int) date('Y');
        for ($i = (int) date('n') - 1, $k = 0; $k < $count; $i = ($i + 11) % 12, $k++) {
            if (array_key_exists($y . '-' . ($i + 1), $tmp)) {
                $return[] = $tmp[$y . '-' . ($i + 1)];
            } else {
                $return[] = [
                    'm' => $i + 1,
                    'y' => $y,
                    'count' => 0,
                ];
            }

            if ($i == 11) {
                $y--;
            }
        }

        return array_reverse($return);
    }

    public function getSearchCount(): int
    {
        $this->flushSQL();
        $this->sql->select('COUNT(*) as count');
        $return = $this->sql->getArray();
        return (int) $return[0]['count'];
    }

    public function createTestData(): void
    {
        $this->flushSQL();
        $str = 'Wir bieten Ihnen leckeres Essen, frische Steinofenpizza, verschiedene Pastavariationen und frische Salate für die ganze Familie, Drinks in geselliger Runde oder an unserer Bar, einen gemütlichen Biergarten, Fremdenzimmer zu fairen Preisen und ab sofort auch Pizza auf Bestellung. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.';

        $terms = array_unique(preg_split('~\W+~ismu', $str));

        for ($i = 0; $i <= 100000; $i++) {
            $this->insert($terms[rand(0, count($terms) - 1)], rand(0, 8), date('Y-m-d H:i:s', time() - (mt_rand(30000, 100000) * rand(0, 700))));
        }
    }
}
