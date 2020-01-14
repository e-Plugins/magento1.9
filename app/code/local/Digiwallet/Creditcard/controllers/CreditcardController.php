<?php

/**
 *
 *	DigiWallet Plugins
 *  DigiWallet Plugin v2.2 for Magento 1.4+
 *
 *  (C) Copyright Target Media B.V 2019
 *
 * 	@file 		iDEAL Model
 *	@author		Target Media B.V. / https://digiwallet.nl
 *  
 *  v2.1	Added pay by invoice
 *  v2.2 	Added creditcards 
 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "digiwallet.class.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "Base_Digiwallet_Controller.php");

class Digiwallet_Creditcard_CreditcardController extends Base_Digiwallet_Controller
{

    protected $_code = 'creditcard';

    protected $_tp_method = 'CC';
}

?>
