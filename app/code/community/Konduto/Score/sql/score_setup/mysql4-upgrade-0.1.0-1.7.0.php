<?php

$installer = $this;

$installer->startSetup();

$installer->run("

CREATE INDEX `score_order_no_idx`
ON {$this->getTable('score')} (`order_no`);

");

$installer->endSetup(); 