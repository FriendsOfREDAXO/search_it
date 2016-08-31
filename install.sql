CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%searchit_index` (
  `id` int(11) NOT NULL auto_increment,
  `fid` varchar(255) NULL,
  `catid` int(11) NULL,
  `ftable` varchar(255) NULL,
  `fcolumn` varchar(255) NULL,
  `texttype` varchar(255) NOT NULL,
  `clang` int(11) NULL,
  `filename` varchar(255) NULL,
  `fileext` varchar(255) NULL,
  `plaintext` longtext NOT NULL default '',
  `unchangedtext` longtext NOT NULL default '',
  `teaser` longtext NOT NULL default '',
  `values` longtext NOT NULL default '',
  PRIMARY KEY (`id`),
  INDEX (`fid`),
  FULLTEXT (`plaintext`),
  FULLTEXT (`unchangedtext`),
  FULLTEXT (`plaintext`,`unchangedtext`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `%TABLE_PREFIX%searchit_index` CHANGE COLUMN fid fid varchar(255) NULL;
/* ALTER TABLE `%TABLE_PREFIX%searchit_index` ADD COLUMN `values` text NOT NULL default ''; */

/*DROP TRIGGER IF EXISTS minfid;
CREATE TRIGGER minfid BEFORE INSERT ON `%TABLE_PREFIX%searchit_index`
FOR EACH ROW
  SET NEW.fid = CASE WHEN NEW.fid IS NULL THEN (SELECT IF(IFNULL(MIN(fid), 0) > 0, 0, IFNULL(MIN(fid), 0)) FROM `%TABLE_PREFIX%searchit_index`) - 1 ELSE NEW.fid END;*/

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%searchit_cache` (
  `id` int(11) NOT NULL auto_increment,
  `hash` char(32) NOT NULL,
  `returnarray` longtext NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%searchit_cacheindex_ids` (
  `id` int(11) NOT NULL auto_increment,
  `index_id` int(11) NULL,
  `cache_id` varchar(255) NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `%TABLE_PREFIX%searchit_keywords` (
  `id` int(11) NOT NULL auto_increment,
  `keyword` varchar(255) NOT NULL,
  `soundex` varchar(255) NOT NULL,
  `metaphone` varchar(255) NOT NULL,
  `colognephone` varchar(255) NOT NULL,
  `clang` int(11) NOT NULL DEFAULT -1,
  `count` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY  (`id`),
  UNIQUE (`keyword`,`clang`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
