<?php

/**

	DigiWallet Plugins
    DigiWallet Plugin v0.0.1 for Magento 1.4+

    (C) Copyright Yellow Melon 2013

 	@file 		Sofort country selector
	@author		Target Media B.V. / https://digiwallet.nl

 */

require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS. "Digiwallet" . DS . "digiwallet.class.php");

class Digiwallet_Sofort_Block_Payment_Form_Sofort extends Mage_Payment_Block_Form_Cc
	{
    protected $_tp_method = "DEB";

    protected function _construct() {
        parent::_construct();
		$this->setTemplate('sofort/payment/form/sofort.phtml');
		}

	}
