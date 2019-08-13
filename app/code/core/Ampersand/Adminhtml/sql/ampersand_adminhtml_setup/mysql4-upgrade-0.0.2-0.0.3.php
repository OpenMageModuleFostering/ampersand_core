<?php

$this->startSetup();

$this->run("
    
ALTER TABLE `{$this->getTable('ampersand_adminhtml/search')}`
  ADD COLUMN `size_select_sql` TEXT NOT NULL,
  ADD COLUMN `neighbour_items_count_select_sql` TEXT NOT NULL,
  ADD COLUMN `collection_item_id_field` VARCHAR(24) NOT NULL
;
    
");

$this->endSetup();