CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%search_it_stats_searchterms` (
  `id` int(11) NOT NULL auto_increment,
  `term` varchar(255) NOT NULL,
  `time` datetime NOT NULL,
  `resultcount` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;
