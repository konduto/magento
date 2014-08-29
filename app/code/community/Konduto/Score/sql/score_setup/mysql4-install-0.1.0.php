<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('score')};
CREATE TABLE {$this->getTable('score')} (
  `score_id` int(11) unsigned NOT NULL auto_increment,
  `order_no` int(128) NOT NULL,
  `request` longtext NOT NULL default '',
  `response` longtext NOT NULL default '',
  `score` varchar(128) NOT NULL default '',
  `recommendation` varchar(128) NOT NULL default '',
  `visitor_id` varchar(128) NOT NULL default '',
  `created_time` datetime NULL,
  `update_time` datetime NULL,
  PRIMARY KEY (`score_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

$installer->endSetup(); 