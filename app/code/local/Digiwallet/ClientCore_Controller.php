<?php
require_once (BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "ClientCore_Model.php");

class ClientCore_Controller extends Mage_Core_Controller_Front_Action
{

    public $errorMsg;

    public $message;

    /**
     * Handle redirect that starts DigiWallet payment
     */
    public function redirectAction()
    {
        /** @var ClientCore_Model $payModel */
        $payModel = Mage::getSingleton($this->_code . '/' . $this->_code);
        $payUrl = $payModel->setupPayment();
        if (! empty($payUrl)) {
            header('Location: ' . $payUrl);
            exit();
        }
        if (! $payUrl) { // Fail and restore cart
            $this->_redirect('checkout/onepage');
        }
    }

    /**
     * Handle return URL
     */
    public function returnAction()
    {
        $trxid = $this->getRequest()->get('trxid');
        if(empty($trxid)) {
            $trxid = $this->getRequest()->get('transactionID');
        }

        $orderId = (int) $this->getRequest()->get('order_id');

        // Call report first
        if (! $this->execReport($orderId, $trxid)) {
            // Fail case
            Mage::getSingleton('core/session')->addError($this->errorMsg);

            $obj = new ClientCore_Model();
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            $obj->restoreCart($order);

            $this->_redirect('checkout/cart');
        } else {
            Mage::getSingleton('core/session')->addSuccess($this->message);
            $this->_redirect('checkout/onepage/success');
        }
    }

    private function execReport($orderId, $trxid)
    {
        if (empty($trxid)) {
            $this->errorMsg = 'Transaction txid missing';
            return false;
        }
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');
        $sql = "SELECT `paid` FROM `digiwallet` WHERE `digiwallet_txid` = " . $write->quote($trxid) . " AND method=" . $write->quote($this->_tp_method);

        $result = Mage::getSingleton('core/resource')->getConnection('core_read')->fetchAll($sql);

        if (! count($result)) {
            $this->errorMsg = 'Transaction not found';
            return false;
        }
        if (! empty($result[0]['paid'])) {
            $this->message = 'Callback already processed';
            return true;
        }

        $language = (Mage::app()->getLocale()->getLocaleCode() == 'nl_NL') ? "nl" : "en";
        $digiWallet = new \Digiwallet\ClientCore(Mage::getStoreConfig("payment/$this->_code/rtlo"), $this->_tp_method, $language);

        /** @var \Digiwallet\client\src\Response\CheckTransaction $paymentStatus */
        $paymentStatus = $digiWallet->checkTransaction(Mage::getStoreConfig("payment/$this->_code/token"), $trxid);

        if ($paymentStatus) {
            $sql = "UPDATE `digiwallet` SET `paid` = now() WHERE `order_id` = '" . $orderId . "' AND method='" . $this->_tp_method . "' AND `digiwallet_txid` = '" . $trxid . "'";
            Mage::getSingleton('core/resource')->getConnection('core_write')->query($sql);

            $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
            if (!in_array($order->getState(), [Mage_Sales_Model_Order::STATE_PROCESSING, Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW])) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $newState = Mage_Sales_Model_Order::STATE_PROCESSING;
                $orderGrandTotal = $order->getGrandTotal();

                if (! $invoice->getTotalQty()) {
                    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
                }

                $invoice->register();
                $invoice->sendEmail();

                $transaction = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
                $transaction->save();

                // Add transaction for refund.
                $payment = $order->getPayment();
                $payment->setTransactionId($trxid)
                    ->setCurrencyCode()
                    ->setPreparedMessage('message')
                    ->setShouldCloseParentTransaction(true)
                    ->setIsTransactionClosed(0)
                    ->registerCaptureNotification($orderGrandTotal);

                $order->setStatus('Processing');
                $order->setIsInProcess(true);
                $order->setState($newState, true, 'Invoice #' . $invoice->getIncrementId() . ' created.');
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
                $order->save();

                $this->message = 'Callback has been processed';
            } else {
                $this->message = "Already completed, skipped... ";
            }

            return true;
        } else {
            $this->errorMsg = "Error in payment processing " . $digiWallet->getErrorMessage();
            return false;
        }
    }

    /**
     * Handle report URL
     */
    public function reportAction()
    {
        $orderId = (int) $this->getRequest()->get('order_id');

        $trxid = (string) $this->getRequest()->getPost('trxid', null);
        if(empty($trxid)) {
            $trxid = (string) $this->getRequest()->getPost('transactionID', null);
        }

        if (! $this->execReport($orderId, $trxid)) {
            echo $this->errorMsg;
        }

        echo $this->message;
        echo "(Magento, 06-09-2016)";
    }
}