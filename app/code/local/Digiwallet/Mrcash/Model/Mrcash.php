<?php

/**
 *
 *  DigiWallet Plugins
 *  DigiWallet Plugin v2.1 for Magento 1.4+
 *
 *  (C) Copyright Target Media B.V 2019
 *
 *  @file       iDEAL Model
 *  @author         Target Media B.V. / https://digiwallet.nl
 *
 *  v2.1    Added pay by invoice
 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "digiwallet.class.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "Base_Digiwallet_Model.php");

class Digiwallet_Mrcash_Model_Mrcash extends Base_Digiwallet_Model
{

    protected $_code = 'mrcash';

    protected $_isGateway = true;

    protected $_canAuthorize = true;

    protected $_canCapture = true;

    protected $_canCapturePartial = true;

    protected $_canRefund = true;

    protected $_canVoid = true;

    protected $_canUseInternal = true;

    protected $_canUseCheckout = true;

    protected $_canUseForMultishipping = true;

    protected $_canSaveCc = false;

    protected $_tp_method = "MRC";

    /**
     * Prepare redirect that starts DigiWallet payment
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('mrcash/mrcash/redirect', array(
            '_secure' => true,
            'bank_id' => $_POST["payment"]["bank_id"]
        ));
    }
}
