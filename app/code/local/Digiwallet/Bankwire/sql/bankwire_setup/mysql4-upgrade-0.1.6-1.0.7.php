<?php
// here are the table creation for this module e.g.:
$this->startSetup();

$this->run("RENAME TABLE `targetpay` TO `digiwallet`");

$this->endSetup();
