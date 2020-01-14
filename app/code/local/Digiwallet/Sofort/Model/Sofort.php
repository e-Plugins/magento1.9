<?php

/**
 *
 *	DigiWallet Plugins
 *  DigiWallet Plugin v2.1 for Magento 1.4+
 *
 *  (C) Copyright Target Media B.V 2019
 *
 * 	@file 		iDEAL Model
 *	@author		Target Media B.V. / https://digiwallet.nl
 *  
 *  v2.1	Added pay by invoice
 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "digiwallet.class.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "Base_Digiwallet_Model.php");

class Digiwallet_Sofort_Model_Sofort extends Base_Digiwallet_Model
{

    protected $_code = 'sofort';

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

    protected $_tp_method = "DEB";

    protected $_formBlockType = 'sofort/payment_form_sofort';

    /**
     * Prepare redirect that starts DigiWallet payment
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl("$this->_code/$this->_code/redirect", array(
            '_secure' => true,
            'bank_id' => $_POST["payment"]["country_id"]
        ));
    }
}

?>
