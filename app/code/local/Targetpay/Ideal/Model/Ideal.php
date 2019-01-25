<?php

/**
 
 iDEALplugins.nl
 TargetPay plugin v0.0.1 for Magento 1.4+
 
 (C) Copyright Yellow Melon 2013
 
 @file 		iDEAL Model
 @author		Yellow Melon B.V. / www.idealplugins.nl
 
 */
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Targetpay" . DS . "targetpay.class.php");
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Targetpay" . DS . "Base_Targetpay_Model.php");

class Targetpay_Ideal_Model_Ideal extends Base_Targetpay_Model
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
     * Prepare redirect that starts TargetPay payment
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
