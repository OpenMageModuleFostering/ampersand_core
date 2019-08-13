<?php

$this->startSetup();

$this->run("
    
CREATE TABLE `{$this->getTable('ampersand_adminhtml/search')}` (
  `entity_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `single_item_select_sql` TEXT NOT NULL,
  `neighbour_items_select_sql` TEXT NOT NULL,
  `created_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `order_part` TEXT NOT NULL,
  `size` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`entity_id`),
  KEY (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `{$this->getTable('ampersand_adminhtml/search_item')}` (
  `search_item_id` CHAR(20),
  `search_id` INT UNSIGNED NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `position` INT UNSIGNED NOT NULL,
  `collection_item_id` VARCHAR(32),
  `prev_collection_item_id` VARCHAR(32),
  `next_collection_item_id` VARCHAR(32),
  PRIMARY KEY (`search_item_id`),
  KEY (`expires_at`),
  KEY (`position`),
  UNIQUE KEY (`search_id`, `collection_item_id`),
  CONSTRAINT `AMPRSND_ADMNHTML_SRCH_ITM_SRCH_ID_SRCH_ENTTY_ID` FOREIGN KEY (`search_id`)
    REFERENCES `{$this->getTable('ampersand_adminhtml/search')}` (`entity_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

# select ... from ... join ... where `field` %s ... order by `field` %s ...
# select ... from ... join ... where `field` %s ...
    
");

$this->endSetup();