--
-- Table structure for table `locum_covercache`
--

CREATE TABLE IF NOT EXISTS `locum_covercache` (
  `bnum` int(12) unsigned NOT NULL,
  `cover_stdnum` varchar(16) NOT NULL,
  `uid` int(10) unsigned NOT NULL,
  `updated` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  PRIMARY KEY  (`bnum`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;