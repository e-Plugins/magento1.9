<?php
// here are the table creation for this module e.g.:
$this->startSetup();

$this->run("ALTER TABLE  `digiwallet` ADD  `more` VARCHAR( 255 ) NOT NULL AFTER  `digiwallet_txid`");

$this->endSetup();
