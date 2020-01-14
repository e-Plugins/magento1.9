<?php

/**
 
 DigiWallet Plugins
 DigiWallet Plugin v0.0.1 for Magento 1.4+
 
 (C) Copyright Yellow Melon 2013
 
 @file 		iDEAL Model
 @author		Target Media B.V. / https://digiwallet.nl
 
 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "digiwallet.class.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "Base_Digiwallet_Model.php");

class Digiwallet_Ideal_Model_Ideal extends Base_Digiwallet_Model
{

    protected $_code = 'ideal';

    protected $_isGateway = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canCapturePartial = false;

    protected $_canRefund = true;

    protected $_canVoid = true;

    protected $_canUseInternal = true;

    protected $_canUseCheckout = true;

    protected $_canUseForMultishipping = true;

    protected $_canSaveCc = false;

    protected $_tp_method = "IDE";

    protected $_formBlockType = 'ideal/payment_form_ideal';

    /**
     * Prepare redirect that starts DigiWallet payment
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('ideal/ideal/redirect', array(
            '_secure' => true,
            'bank_id' => $_POST["payment"]["bank_id"]
        ));
    }
}

?>
