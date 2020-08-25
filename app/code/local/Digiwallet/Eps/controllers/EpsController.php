<?php

/**
 *
 *  DigiWallet Plugins
 *  DigiWallet Plugin v2.1 for Magento 1.4+
 *
 *  (C) Copyright Target Media B.V 2019
 *
 *  @file     iDEAL Model
 *  @author   Target Media B.V. / https://digiwallet.nl
 *  
 *  v2.1  Added pay by invoice
 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "ClientCore.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "ClientCore_Controller.php");

class Digiwallet_Eps_EpsController extends ClientCore_Controller
{

    protected $_code = 'eps';

    protected $_tp_method = 'Eps';
}

?>
