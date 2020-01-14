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
require_once(BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "digiwallet.class.php");
require_once(BP . DS . 'app' . DS . 'code' . DS . 'local' . DS . "Digiwallet" . DS . "Base_Digiwallet_Model.php");

class Digiwallet_Afterpay_Model_Afterpay extends Base_Digiwallet_Model
{

    protected $_code = 'afterpay';

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

    protected $_tp_method = "AFP";

    public $errorMsg;

    /**
     * Prepare redirect that starts DigiWallet payment
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('afterpay/afterpay/redirect', array(
            '_secure' => true,
            'bank_id' => $_POST["payment"]["bank_id"]
        ));
    }

    /**
     *
     * @param unknown $country
     * @param unknown $phone
     * @return unknown
     */
    private static function format_phone($country, $phone)
    {
        $function = 'format_phone_' . strtolower($country);
        if (method_exists('Digiwallet_Afterpay_Model_Afterpay', $function)) {
            return self::$function($phone);
        }
        else {
            echo "unknown phone formatter for country: " . $function;
            exit();
        }
        return $phone;
    }

    /**
     *
     * @param unknown $phone
     * @return string|mixed
     */
    private static function format_phone_nld($phone)
    {
        // note: making sure we have something
        if (!isset($phone{3})) {
            return '';
        }
        // note: strip out everything but numbers
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);
        switch ($length) {
            case 9:
                return "+31" . $phone;
                break;
            case 10:
                return "+31" . substr($phone, 1);
                break;
            case 11:
            case 12:
                return "+" . $phone;
                break;
            default:
                return $phone;
                break;
        }
    }

    /**
     *
     * @param unknown $phone
     * @return string|mixed
     */
    private static function format_phone_bel($phone)
    {
        // note: making sure we have something
        if (!isset($phone{3})) {
            return '';
        }
        // note: strip out everything but numbers
        $phone = preg_replace("/[^0-9]/", "", $phone);
        $length = strlen($phone);
        switch ($length) {
            case 9:
                return "+32" . $phone;
                break;
            case 10:
                return "+32" . substr($phone, 1);
                break;
            case 11:
            case 12:
                return "+" . $phone;
                break;
            default:
                return $phone;
                break;
        }
    }

    /**
     *
     * @param unknown $street
     * @return NULL[]|string[]|unknown[]
     */
    private static function breakDownStreet($street)
    {
        $out = [
            'street' => null,
            'houseNumber' => null,
            'houseNumberAdd' => null,
        ];
        $addressResult = null;
        preg_match("/(?P<address>\D+) (?P<number>\d+) (?P<numberAdd>.*)/", $street, $addressResult);
        if (!$addressResult) {
            preg_match("/(?P<address>\D+) (?P<number>\d+)/", $street, $addressResult);
        }
        if (empty($addressResult)) {
            $out['street'] = $street;

            return $out;
        }

        $out['street'] = array_key_exists('address', $addressResult) ? $addressResult['address'] : null;
        $out['houseNumber'] = array_key_exists('number', $addressResult) ? $addressResult['number'] : null;
        $out['houseNumberAdd'] = array_key_exists('numberAdd', $addressResult) ? trim(strtoupper($addressResult['numberAdd'])) : null;

        return $out;
    }

    /**
     *
     * @param unknown $order
     * @param DigiwalletCore $digiWallet
     */
    public function additionalParameters($order, DigiwalletCore $digiWallet)
    {
        $billingData = $order->getBillingAddress()->getData();
        $shippingData = $order->getShippingAddress()->getData();

        // Supported countries are: Netherlands (NLD) and in Belgium (BEL)
        $billingCountry = (strtoupper($billingData['country_id']) == 'BE' ? 'BEL' : 'NLD');
        $shippingCountry = (strtoupper($shippingData['country_id']) == 'BE' ? 'BEL' : 'NLD');

        // Exception for popular Dutch checkout plugin that changes the address object's fields
        if (!empty($billingData['adresnummer']) || !empty($billingData['huisnummertoevoeging'])) {
            $streetParts = [
                'street' => $billingData['street'],
                'houseNumber' => $billingData['adresnummer'],
                'houseNumberAdd' => $billingData['huisnummertoevoeging'],
            ];
        }
        else {
            $streetParts = self::breakDownStreet($billingData['street']);
        }

        $digiWallet->bindParam('billingstreet', $streetParts['street']);
        $digiWallet->bindParam('billinghousenumber', $streetParts['houseNumber'] . ' ' . $streetParts['houseNumberAdd']);
        $digiWallet->bindParam('billingpostalcode', $billingData['postcode']);
        $digiWallet->bindParam('billingcity', $billingData['city']);
        $digiWallet->bindParam('billingpersonemail', $billingData['email']);
        $digiWallet->bindParam('billingpersoninitials', "");
        $digiWallet->bindParam('billingpersongender', "");
        $digiWallet->bindParam('billingpersonfirstname', $billingData['firstname']);
        $digiWallet->bindParam('billingpersonsurname', $billingData['lastname']);
        $digiWallet->bindParam('billingcountrycode', $billingCountry);
        $digiWallet->bindParam('billingpersonlanguagecode', $billingCountry);
        $digiWallet->bindParam('billingpersonbirthdate', "");
        $digiWallet->bindParam('billingpersonphonenumber', self::format_phone($billingCountry, $billingData['telephone']));

        // Exception for popular Dutch checkout plugin that changes the address object's fields
        if (!empty($shippingData['adresnummer']) || !empty($shippingData['huisnummertoevoeging'])) {
            $streetParts = [
                'street' => $shippingData['street'],
                'houseNumber' => $shippingData['adresnummer'],
                'houseNumberAdd' => $shippingData['huisnummertoevoeging'],
            ];
        }
        else {
            $streetParts = self::breakDownStreet($shippingData['street']);
        }

        $digiWallet->bindParam('shippingstreet', $streetParts['street']);
        $digiWallet->bindParam('shippinghousenumber', $streetParts['houseNumber'] . ' ' . $streetParts['houseNumberAdd']);
        $digiWallet->bindParam('shippingpostalcode', $shippingData['postcode']);
        $digiWallet->bindParam('shippingcity', $shippingData['city']);
        $digiWallet->bindParam('shippingpersonemail', $shippingData['email']);
        $digiWallet->bindParam('shippingpersoninitials', "");
        $digiWallet->bindParam('shippingpersongender', "");
        $digiWallet->bindParam('shippingpersonfirstname', $shippingData['firstname']);
        $digiWallet->bindParam('shippingpersonsurname', $shippingData['lastname']);
        $digiWallet->bindParam('shippingcountrycode', $shippingCountry);
        $digiWallet->bindParam('shippingpersonlanguagecode', $shippingCountry);
        $digiWallet->bindParam('shippingpersonbirthdate', "");
        $digiWallet->bindParam('shippingpersonphonenumber', self::format_phone($shippingCountry, $shippingData['telephone']));

        $digiWallet->bindParam('test', (bool)Mage::getStoreConfig('payment/afterpay/testmode'));

        /*
         * Start converting all the entries in this order to invoice lines that are acceptable to AfterPay
         * This means in practice we list every separate product in this order as well as the shipping fee
         * and any potential payment method fee that we can automatically identify. Any remaining amount
         * is aggregated in a "Other fees" invoice line.
         *
         * Take note that all of these amounts are EXCLUDING tax and instead a tax code ID is set per invoice line.
         * AfterPay does their VAT calculation themselves.
         */

        // Getting the items in the order
        $order_items = $order->getAllItems();
        $invoicelines = [];
        $store = Mage::app()->getStore('default');
        $totalProductAmountIncludingTax = 0;
        $totalProductAmountExcludingTax = 0;
        // Iterating through each item in the order
        foreach ($order_items as $item_data) {
            $product_name = $item_data->getName();
            $item_quantity = $item_data->getQtyOrdered();
            $itemTotalExcludingTax = (int)$item_quantity * (float)$item_data->getPrice();
            $itemTotalIncludingTax = (int)$item_quantity * (float)$item_data->getPriceInclTax();

            $already_added = false;
            foreach ($invoicelines as $invoiceline) {
                if($invoiceline['productCode'] == $item_data->getSku()) {
                    $already_added = true;
                }
            }

            if($already_added && $itemTotalIncludingTax == 0) {
                continue;
            }

            $invoicelines[] = [
                'productCode' => (string)$item_data->getSku(),
                'productDescription' => $product_name,
                'quantity' => (int)$item_quantity,
                'price' => $itemTotalIncludingTax,
                'taxCategory' => $digiWallet->getTax($item_data->getTaxPercent())
            ];

            $totalProductAmountIncludingTax += $itemTotalIncludingTax;
            $totalProductAmountExcludingTax += $itemTotalExcludingTax;
        }



        $invoicelines[] = [
            'productCode' => 'SHIPPING',
            'productDescription' => "Shipping costs",
            'quantity' => 1,
            'price' => $order->getShippingInclTax(),
            'taxCategory' => 1
        ];

        // The remainder is everything we have not processed into an invoice line yet
        $remainderIncludingTax = $order->getGrandTotal() - $totalProductAmountIncludingTax - $order->getShippingInclTax();
        // See if we can recognize a part of the order as a popular payment fee extension
        $paymentFeeLine = $this->getPopularPaymentFeeLine($order);
        // In case we recognize a payment method fee from a popular extension we will add it separately
        // If not, it will be listed in the Other fees invoice line
        if (!empty($paymentFeeLine)) {
            $invoicelines[] = $paymentFeeLine;
            // And subtract it from the remainder
            $paymentFeeIncludingTax = $paymentFeeLine['price'];
            // $paymentFeeVatRate = $this->taxCategoryToVATRate($paymentFeeLine['taxCategory']); // e.g. 21
            // $paymentFeeVatFactor = $paymentFeeVatRate / 100; // e.g. 0.21
            // $paymentFeeIncludingTax = $paymentFeeExcludingTax + ($paymentFeeExcludingTax * $paymentFeeVatFactor);
            $remainderIncludingTax -= $paymentFeeIncludingTax;
        }
        // In case there are any other fees we do not automatically recognize, we will list them in a stack and assume high VAT
        $remainderIncludingTax = round($remainderIncludingTax, 2);
        if (!empty($remainderIncludingTax)) {
            // $remainderExcludingTax = $remainderIncludingTax - ($remainderIncludingTax * 0.21);
            $invoicelines[] = [
                'productCode' => 'OTHER',
                'productDescription' => "Other fees",
                'quantity' => 1,
                'price' => $remainderIncludingTax,
                'taxCategory' => 1
            ];
        }

        $digiWallet->bindParam('invoicelines', json_encode($invoicelines));
        $digiWallet->bindParam('userip', $_SERVER["REMOTE_ADDR"]);
    }

    /**
     * @param $taxCategory
     * @return int
     */
    protected function taxCategoryToVATRate($taxCategory)
    {
        switch ($taxCategory) {
            case 1: // High rate
                return 21;
            case 2: // Low rate
                return 6;
            case 4: // No rate
            case 3: // 0% rate
            default:
                return 0;
        }
    }

    protected function getPopularPaymentFeeLine($order)
    {
        // Check if AfterPay Fee is used for service fee
        if (Mage::helper('core')->isModuleEnabled('Afterpay_Afterpayfee')) {
            $paymentFee = (float)$order->getAfterpayfeeAmount();
            if (!empty($paymentFee)) {
                $paymentFeeLine = array(
                    'productDescription' => Mage::getStoreConfig(
                        'afterpay/afterpay_afterpayfee/afterpayfee_label',
                        $order->getStoreId()
                    ),
                    'productCode' => 'PAYMENTFEE',
                    'price' => round($paymentFee, 2),
                    'taxCategory' => 1,
                    'quantity' => 1,
                );
                return $paymentFeeLine;
            }
        }

        // Check if Fooman Surcharge is used for service fee
        if (Mage::helper('core')->isModuleEnabled('Fooman_Surcharge')) {
            $paymentFee = $order->getFoomanSurchargeAmount(); // + $this->_order->getFoomanSurchargeTaxAmount();
            if (!empty($paymentFee)) {
                $paymentFeeLine = array(
                    'productDescription' => $order->getFoomanSurchargeDescription(),
                    'productCode' => 'PAYMENTFEE',
                    'price' => round($paymentFee, 2),
                    'taxCategory' => 1,
                    'quantity' => 1,
                );
                return $paymentFeeLine;
            }
        }

        // Check if Mageworx Multifees is used for service fee
        if (Mage::helper('core')->isModuleEnabled('MageWorx_MultiFees')) {
            $paymentFee = (float)($order->getMultifeesAmount());
            if (!empty($paymentFee)) {
                $paymentFeeLine = array(
                    'productDescription' => 'Service fee',
                    'productCode' => 'PAYMENTFEE',
                    'price' => round($paymentFee, 2),
                    'taxCategory' => 1,
                    'quantity' => 1,
                );
                return $paymentFeeLine;
            }
        }

        return false;
    }

}

?>
