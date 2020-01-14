<?php

class Base_Digiwallet_Model extends Mage_Payment_Model_Method_Abstract
{
    public $rtlo;

    public function __construct()
    {
        $this->rtlo = Mage::getStoreConfig('payment/' . $this->_code . '/rtlo');
    }
    /**
     *
     * @param string $bankId
     * @return boolean
     */
    public function setupPayment($bankId = false)
    {
        $lastOrderId = Mage::getSingleton('checkout/session')->getLastOrderId();
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($lastOrderId);
        $errMsg = '';

        if (! $order->getId()) {
            Mage::throwException('Cannot load order #' . $lastOrderId);
        }

        $orderId = $order->getRealOrderId();
        $language = (Mage::app()->getLocale()->getLocaleCode() == 'nl_NL') ? "nl" : "en";
        $digiWallet = new DigiwalletCore($this->_tp_method, $this->rtlo, $language, false);
        $digiWallet->setAmount(round($order->getGrandTotal() * 100));
        $digiWallet->setDescription("Order #" . $orderId);

        if($bankId) {
            if($this->_code == 'sofort') {
                $digiWallet->setCountryId($bankId);
            }
            $digiWallet->setBankId($bankId);

        }

        $digiWallet->setReturnUrl(Mage::getUrl("$this->_code/$this->_code/return", array(
            '_secure' => true,
            'order_id' => $orderId
        )));
        $digiWallet->setReportUrl(Mage::getUrl("$this->_code/$this->_code/report", array(
            '_secure' => true,
            'order_id' => $orderId
        )));

        if($this->_code == 'bankwire' || $this->_code == 'afterpay') {
            $this->additionalParameters($order, $digiWallet); // Adding extra info for Bankwire startAPI
        }

        // Consumer'e email address
        /** @var string  $consumer_email */
        $consumer_email = $order->customer_email;
        if(empty($consumer_email)) {
            if(!empty($order->getCustomerEmail())) {
                $consumer_email = $order->getCustomerEmail();
            } else {
                $billingData = $order->getBillingAddress()->getData();
                $shippingData = $order->getShippingAddress()->getData();
                if(isset($billingData['email']) && !empty($billingData['email'])) {
                    $consumer_email = $billingData['email'];
                } else if(isset($shippingData['email']) && !empty($shippingData['email'])) {
                    $consumer_email = $shippingData['email'];
                }
            }
        }

        if(!empty($consumer_email)) {
            $digiWallet->bindParam('email', $consumer_email);
        }

        $bankUrl = $digiWallet->startPayment();

        if (! $bankUrl) {
            $errMsg .= "Digiwallet error: " . $digiWallet->getErrorMessage();
        }

        if ($errMsg) {
            $this->restoreCart($order); // restore cart because magento automatically clear cart after place order
            Mage::getSingleton('core/session')->addError($errMsg);
            return false;
        }

        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        // Check table Digiwallet is exists or not
        $readConnection = Mage::getSingleton('core/resource')->getConnection('core_read');
        $query = "SHOW TABLES LIKE 'digiwallet'";
        $resultCheck = $readConnection->fetchAll($query);
        if (empty($resultCheck)) {
            $sql = "CREATE TABLE IF NOT EXISTS `digiwallet` (
                    `order_id` VARCHAR(64),
                    `method` VARCHAR(6) DEFAULT NULL,
                    `digiwallet_txid` VARCHAR(64) DEFAULT NULL,
                    `digiwallet_response` VARCHAR(128) DEFAULT NULL,
                    `more` VARCHAR( 255 ),
                    `paid` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`order_id`));";
            $write->query($sql);
        }

        $write->query("INSERT INTO `digiwallet`
            SET `order_id`=" . $write->quote($orderId) . ",
                `method`=" . $write->quote($this->_tp_method) . ",
                `digiwallet_txid`=" . $write->quote($digiWallet->getTransactionId()) . ",
                `more` = " . $write->quote($digiWallet->getMoreInformation()));

        if($this->_code == 'bankwire') {
            return $lastOrderId;
        }

        return $bankUrl;
    }

    /**
     * Restore cart when processing fail
     *
     * @param object $order
     */
    public function restoreCart($order)
    {
        $cart = Mage::getSingleton('checkout/cart');
        $items = $order->getItemsCollection();
        foreach ($items as $item) {
            try {
                $cart->addOrderItem($item);
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::helper('checkout')->__('Cannot add the item to shopping cart.');
            }
        }

        $cart->save();
    }

    /**
     *
     * {@inheritdoc}
     *
     * @see Mage_Payment_Model_Method_Abstract::refund()
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $order = $payment->getOrder();
        $creditmemo = Mage::app()->getRequest()->getParam("creditmemo");
        $dataRefund = array(
            'paymethodID' => $this->_tp_method,
            'transactionID' => $payment->getLastTransId(),
            'amount' => intval(floatval($amount * 100)),
            'description' => $creditmemo["comment_text"],
            'internalNote' => 'Internal note - OrderId: ' . $order->getIncrement_id() . ', Amount: ' . $amount . ', InvoiceID: ' . $payment->getCreditmemo()
                    ->getInvoice()
                    ->getIncrement_id() . ', Customer Email: ' . $order->getCustomer_email(),
            'consumerName' => $payment->getOrder()->getCustomer_firstname() . ' ' . $payment->getOrder()->getCustomer_lastname()
        );

        $digiWallet = new DigiwalletCore($this->_tp_method, $this->rtlo);

        if (! $digiWallet->refund(Mage::getStoreConfig("payment/$this->_code/token"), $dataRefund)) {
            Mage::throwException($digiWallet->getErrorMessage());
        }

        return $this;
    }
}
