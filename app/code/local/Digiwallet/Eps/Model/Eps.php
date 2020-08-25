<?php

/**

DigiWallet Plugins
DigiWallet Plugin v0.0.1 for Magento 1.4+

(C) Copyright Yellow Melon 2013

@file 		Eps Model
@author		Target Media B.V. / https://digiwallet.nl

 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "ClientCore.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "ClientCore_Model.php");

class Digiwallet_Eps_Model_Eps extends ClientCore_Model
{

    protected $_code = 'eps';

    protected $_isGateway = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canCapturePartial = false;

    protected $_canRefund = false;  //Not support

    protected $_canVoid = true;

    protected $_canUseInternal = true;

    protected $_canUseCheckout = true;

    protected $_canUseForMultishipping = true;

    protected $_canSaveCc = false;

    protected $_tp_method = "Eps";

    /**
     * Prepare redirect that starts DigiWallet payment
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('eps/eps/redirect', array(
            '_secure' => true,
        ));
    }
}

?>
