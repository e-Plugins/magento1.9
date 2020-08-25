<?php

class ClientCore_Model extends Mage_Payment_Model_Method_Abstract
{
    public $rtlo;

    public function __construct()
    {
        $this->rtlo = Mage::getStoreConfig('payment/' . $this->_code . '/rtlo');
    }

    /**
     *
     * @return boolean
     * @throws Exception
     * @throws Mage_Core_Exception
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function setupPayment()
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
        $digiWallet = new \Digiwallet\ClientCore($this->rtlo, $this->_tp_method, $language);

        $amount = round($order->getGrandTotal() * 100);
        $description = "Order #" . $orderId;

        $returnUrl = Mage::getUrl("$this->_code/$this->_code/return", array(
            '_secure' => true,
            'order_id' => $orderId
        ));
        $reportUrl = Mage::getUrl("$this->_code/$this->_code/report", array(
            '_secure' => true,
            'order_id' => $orderId
        ));

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

        $formData = array(
            'amount' => $amount,
            'inputAmount' => $amount,
            'consumerEmail' => $consumer_email,
            'description' => $description,
            'returnUrl' => $returnUrl,
            'reportUrl' => $reportUrl,
            'test' => 0
        );


        /** @var \Digiwallet\client\src\Response\CreateTransaction $returnObj */
        $returnObj = $digiWallet->createTransaction(Mage::getStoreConfig("payment/$this->_code/token"), $formData);

        if (!$returnObj) {
            $errMsg .= $digiWallet->getErrorMessage();
        } else if($returnObj->status() != 0) {
            $errMsg .= $digiWallet->getErrorMessage();
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
                `digiwallet_txid`=" . $write->quote($returnObj->transactionId()) . ",
                `more` = " . $write->quote($returnObj->transactionKey()));

        return $returnObj->launchUrl();
    }

    /**
     * Restore cart when processing fail
     *
     * @param object $order
     * @throws Exception
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
        Mage::getSingleton('core/session')->addError("This function is not supported yet!");
        return false;
    }
}
