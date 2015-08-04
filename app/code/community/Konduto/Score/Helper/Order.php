<?php

class Konduto_Score_Helper_Order extends Mage_Core_Helper_Abstract {

    public function getOrderData($order, $payment_status, $visitor = null) {
        if ($visitor == NULL) { $visitor = $this->getVisitorId(); }
        //$odm = Mage::getModel('sales/quote')->load($order->getQuoteId());
        $payment = $this->getPaymentDetails($order);
        if (!$payment['include']) {
          if (Mage::getStoreConfig("scoreoptions/messages/debug")) {
            Mage::log('No credit card detected. Will not send for analysis.', null, 'konduto.log');
          }
          return;
        }
        $payment["status"] = $payment_status;
        unset($payment['include']);
        if ($payment_status === "pending") {
          $order_id = $order->getIncrementId();
        }
        else if ($payment_status === "declined") {
          $order_id = "Try-".$order->getIncrementId()."-".uniqid();
        }
        else {
          if (Mage::getStoreConfig("scoreoptions/messages/debug")) {
            Mage::log('Payment status is not supported:'.$payment_status, null, 'konduto.log');
          }
          return;
        }
        $currency = $order->getQuoteCurrencyCode();
        $billing = $order->getBillingAddress()->getData();
        $shipping = $order->getShippingAddress()->getData();
        if ($order->getCustomerId() == '' || $order->getCustomerId() == NULL) { $customer_id = $order->getCustomerEmail(); }
        else { $customer_id = $order->getCustomerId(); }
        $data['id'] = substr($order_id,0,100);
        $data['total_amount'] = (float) $order->getGrandTotal();
        $data['shipping_amount'] = (float) $order->getShippingAmount();
        $data['tax_amount'] = (float) $order->getTaxAmount();
        $data['currency'] = $currency;
        $data['visitor'] = substr($visitor,0,100);
        $data['customer'] = array(
            'id' => substr($customer_id,0,100),
            'name' => substr($order->getCustomerFirstname() . " " . $order->getCustomerLastname(),0,100),
            'phone1' => substr($billing['telephone'],0,100),
            'email' => substr($order->getCustomerEmail(),0,100)
        );
        if (!($order->getCustomerTaxvat() == NULL || $order->getCustomerTaxvat() == " ")) {
            $data['customer']['tax_id'] = substr($order->getCustomerTaxvat(),0,100);
        }
        $data['payment'][] = $payment;
        $data['billing'] = array(
            'name' => substr($billing['firstname'] . " " . $billing['lastname'],0,100),
            'address1' => substr($billing['street'],0,100),
            'city' => substr($billing['city'],0,100),
            'state' => substr($billing['region'],0,100),
            'zip' => substr($billing['postcode'],0,100),
            'country' => substr($billing['country_id'],0,100)
        );
        $data['shipping'] = array(
            'name' => substr($shipping['firstname'] . " " . $shipping['lastname'],0,100),
            'address1' => substr($shipping['street'],0,255),
            'city' => substr($shipping['city'],0,100),
            'state' => substr($shipping['region'],0,100),
            'zip' => substr($shipping['postcode'],0,100),
            'country' => substr($shipping['country_id'],0,100)
        );
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($item->getQtyToInvoice() > 0) {
                $shopping_cart[] = array(
                    'sku' => substr($item->getSku(),0,100),
                    'product_code' => substr($item->getProductId(),0,100),
                    //'category' => 9999,
                    'name' => substr($item->getName(),0,100),
                    //'unit_cost' => $item->getPrice() * 1,
                    //'quantity' => (int) $item->getQtyToInvoice(),
                );
            }
        }
        if (is_array($shopping_cart)) { $data['shopping_cart'] = $shopping_cart; }
        // removing false, null and blanks
        $data = array_filter($data);
        foreach ($data as $key => $value) {
            if (is_array($value)){
                $data[$key] = array_filter($value);
            }
        }
        $data['analyze'] = ($payment_status === "pending" ? true : false);
        $this->fireRequest($data, $order->getId());
    }

    public function fireRequest($data, $oid = null) {
        if ((!isset($data)) || (!$data)) {
          if (Mage::getStoreConfig("scoreoptions/messages/debug")) {
            Mage::log('No data found. Aborting...', null, 'konduto.log');
          }
          return;
        }
        $data = json_encode($data);
        $header = array();
        $header[] = 'Content-type: application/json';
        $header[] = 'X-Requested-With: Magento v1.7.1';
        $mode = Mage::getStoreConfig('scoreoptions/messages/mode');
        if ($mode == 1) {
            $private = Mage::getStoreConfig('scoreoptions/messages/productionprikey');
            $sslVerifyHost = 2;
            $sslVerifyPeer = true;
        }
        else {
            $private = Mage::getStoreConfig('scoreoptions/messages/sandboxprikey');
            $sslVerifyHost = false;
            $sslVerifyPeer = false;
        }
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.konduto.com/v1/orders',
            CURLOPT_USERPWD => $private,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYHOST => $sslVerifyHost,
            CURLOPT_SSL_VERIFYPEER => $sslVerifyPeer
        ));
        $resp = array();
        try {
            $resp = curl_exec($curl);
        } catch (Exception $ex) {
            $resp = 'curlError = ' . curl_error($curl);
        }
        if (Mage::getStoreConfig("scoreoptions/messages/debug")) {
            Mage::log('request==>' . $data, null, 'konduto.log');
            Mage::log('response=>' . $resp, null, 'konduto.log');
        }
        if (isset($oid)) {
          $save = $this->saveData($data, $resp, $oid);
        }
    }

    public function getPaymentDetails($order, $ret=array()) {
        $payment = $order->getPayment();
        $cc = $payment->getCcNumber();
        $ccNumber = is_numeric($cc) ? $cc : Mage::helper('core')->decrypt($cc);
        $cc_six = substr($ccNumber, 0, 6);
        $ret["type"] = "credit";
        $ret["include"] = false;
        $expmo = sprintf("%02d", $payment->getCcExpMonth());
        $expyear = $payment->getCcExpYear();
        $expyear = (strlen($expyear) == 2 ? "20" . $expyear : $expyear);
        if (($expmo) && ($expyear)) {
          $ret["expiration_date"] = $expmo . $expyear;
        }
        switch ($payment->getMethod()) {
            case 'authorizenet':
                $ret["include"] = true;
                $cards_data = array_values($payment->getAdditionalInformation('authorize_cards'));
                $card_data = $cards_data[0];
                $ret['last4'] = $card_data['cc_last4'];
                break;

            case 'paypal_direct':
                $ret["include"] = true;
                $ret['last4'] = $payment->getCcLast4();
                break;

            case 'sagepaydirectpro':
                $ret["include"] = true;
                $sage = $model->getSagepayInfo();
                $ret['last4'] = $sage->getData('last_four_digits');
                break;

            case 'paypal_express':
            case 'paypal_standard':
                $ret["include"] = true;
                $last4 = null;
                break;

            default:
                $ret["last4"] = substr($ccNumber, -4);
                if (($ret["last4"]) && (strlen($ret["last4"]) == 4)) { $ret["include"] = true; }
                break;
        }

        if ((is_string($cc_six)) && (strlen($cc_six) == 6)) {
            $ret["bin"] = $cc_six;
            $ret["include"] = true;
        }

        return $ret;
    }

    public function getVisitorId($id = NULL) {
        if (isset($_COOKIE['_kdt'])) {
          $cookie = json_decode($_COOKIE['_kdt'], true);
          $id = $cookie['i'];
        }
        return $id;
    }

    public function saveData($data, $resp, $id) {
        $model = Mage::getModel('score/score');
        $collection = $model->getCollection()->addFieldToFilter('order_no', $id);
        if ($collection->getFirstItem()->getScoreId()) {
            $model->setScoreId($collection->getFirstItem()->getScoreId());
        }
        if ($resp != NULL) {
            $response = json_decode($resp, true);
            if (isset($response['order']) && isset($response['order']['score'])) {
                $score = $response['order']['score'];
            } else {
                $score = '';
            }
            if (isset($response['order']) && isset($response['order']['recommendation'])) {
                $recommendation = $response['order']['recommendation'];
            } else {
                $recommendation = '';
            }
            $model->setResponse($resp);
            $model->setRecommendation($recommendation);
            $model->setScore($score);
        }
        if ($data != NULL) {
            $model->setRequest(json_encode($data));
        }
        $model->setOrderNo($id);
        $model->setVisitorId($this->getVisitorId());
        $model->setCreatedTime(now())
                ->setUpdateTime(now());
        try {
            $model->save();
        } catch (Exception $exc) {
            Mage::log('Error while saving data==>' . $exc, null, 'konduto.log');
        }
    }

}