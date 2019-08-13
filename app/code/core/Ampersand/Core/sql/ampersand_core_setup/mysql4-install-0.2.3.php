<?php

$this->startSetup();

$this->run("
    
CREATE TABLE `{$this->getTable('ampersand_core/value')}` (
  `name` VARCHAR(128) NOT NULL,
  `value` TEXT NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

");

$this->endSetup();