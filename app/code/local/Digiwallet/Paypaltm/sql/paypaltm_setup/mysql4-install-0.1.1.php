<?php
// here are the table creation for this module e.g.:
$this->startSetup();
$this->run("
	CREATE TABLE IF NOT EXISTS `digiwallet` (
	`order_id` VARCHAR(64),
    `method` VARCHAR(6) DEFAULT NULL,
	`digiwallet_txid` VARCHAR(64) DEFAULT NULL,
    `digiwallet_response` VARCHAR(128) DEFAULT NULL,
    `paid` DATETIME DEFAULT NULL,
	PRIMARY KEY (`order_id`));
	");

$this->endSetup();
