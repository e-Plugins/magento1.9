<?php
// here are the table creation for this module e.g.:
$this->startSetup();

try{
    $this->run("RENAME TABLE `targetpay` TO `digiwallet`");
} catch (\Exception $ex) {

}

$this->endSetup();
