<?php
$this->startSetup();

$this->run("
	delete from " . Mage::getSingleton('core/resource')->getTableName('core_config_data') . " where path='payment/eps/order_status'
	");

$this->endSetup();