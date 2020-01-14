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

class Digiwallet_Bankwire_Model_Bankwire extends Base_Digiwallet_Model
{

    protected $_code = 'bankwire';

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

    protected $_tp_method = "BW";

    /**
     * Prepare redirect that starts DigiWallet payment
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('bankwire/bankwire/redirect', array(
            '_secure' => true,
            'bank_id' => $_POST["payment"]["bank_id"]
        ));
    }

    /**
     *
     * @param Object $order
     * @param DigiwalletCore $digiWallet
     */
    public function additionalParameters($order, DigiwalletCore $digiWallet)
    {
        $digiWallet->bindParam('salt', $digiWallet->bwSalt);
        $digiWallet->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
    }
}

?>
