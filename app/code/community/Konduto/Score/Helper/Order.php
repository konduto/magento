<?php

class Konduto_Score_Helper_Order extends Mage_Core_Helper_Abstract {

    public function getOrderData($order, $visitor = NULL) {
        if ($visitor == NULL) { $visitor = $this->getVisitorId(); }
        $billing = $order->getBillingAddress()->getData();
        $shipping = $order->getShippingAddress()->getData();
        $od = $order->getId();
        $odm = Mage::getModel('sales/order')->load($od);

        if ($odm->getCustomerId() == '' || $odm->getCustomerId() == NULL) { $customer_id = $odm->getCustomerEmail(); }
        else { $customer_id = $odm->getCustomerId(); }

        $data['id'] = $od;
        $data['total_amount'] = (float) $odm->getGrandTotal();
        $data['shipping_amount'] = (float) $odm->getShippingAmount();
        $data['tax_amount'] = (float) $odm->getTaxAmount();
        $data['currency'] = $odm->getOrderCurrencyCode();
        $data['visitor'] = $visitor;
        $data['ip'] = $odm->getRemoteIp();
        $data['customer'] = array(
            'id' => $customer_id,
            'name' => $odm->getCustomerFirstname() . " " . $odm->getCustomerLastname(),
            'phone1' => $billing['telephone'],
            'email' => $odm->getCustomerEmail()
        );
        if (!($odm->getCustomerTaxvat() == NULL || $odm->getCustomerTaxvat() == " ")) {
            $data['customer']['tax_id'] = $odm->getCustomerTaxvat();
        }
        $data['billing'] = array(
            'name' => $billing['firstname'] . " " . $billing['lastname'],
            'address1' => $billing['street'],
            'city' => $billing['city'],
            'state' => $billing['region'],
            'zip' => $billing['postcode'],
            'country' => $billing['country_id']
        );
        $data['shipping'] = array(
            'name' => $shipping['firstname'] . " " . $shipping['lastname'],
            'address1' => $shipping['street'],
            'city' => $shipping['city'],
            'state' => $shipping['region'],
            'zip' => $shipping['postcode'],
            'country' => $shipping['country_id']
        );

        $paymet = $this->getPaymentDetails($odm);
        if ($paymet['include'] == true) {
            unset($paymet['include']);
            $data['payment'][] = $paymet;
        }
        else { return; }

        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($item->getQtyToInvoice() > 0) {
                $shopping_cart[] = array(
                    'sku' => $item->getSku(),
                    'product_code' => $item->getProductId(),
                    //'category' => 9999,
                    'name' => $item->getName(),
                    //'unit_cost' => $item->getPrice() * 1,
                    //'quantity' => (int) $item->getQtyToInvoice(),
                );
            }
        }
        $data['shopping_cart'] = $shopping_cart;
        // removing null and blanks
        $data = array_filter($data);
        foreach ($data as $key => $value) {
            if(is_array($value)){
                $data[$key] = array_filter($value);
            }
        }

        $response = $this->fireRequest($data);
    }

    public function fireRequest($data) {
        $id = $data['id'];
        $data = json_encode($data);
        $header = array();
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'X-Requested-With: Magento';
        $mode = Mage::getStoreConfig('scoreoptions/messages/mode');
        if ($mode == 1) {
            $private = Mage::getStoreConfig('scoreoptions/messages/productionprikey');
        } else {
            $private = Mage::getStoreConfig('scoreoptions/messages/sandboxprikey');
        }
        $sslVerify = ($mode == 1 ? true : false);
        $pwd = $private;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.konduto.com/v1/orders',
            CURLOPT_USERPWD => $pwd,
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_SSL_VERIFYHOST => $sslVerify,
            CURLOPT_SSL_VERIFYPEER => $sslVerify
        ));
        $resp = array();
        try {
            $resp = curl_exec($curl);
        } catch (Exception $ex) {
            $resp = 'curlError = ' . curl_error($curl);
        }
        $save = $this->saveData($data, $resp, $id);
        if (Mage::getStoreConfig("scoreoptions/messages/debug")) {
            Mage::log('request==>' . $data, NULL, 'konduto.log');
            Mage::log('response==>' . $resp, null, 'konduto.log');
        }
        return $resp;
    }

    public function getPaymentDetails($model) {
        $payment = $model->getPayment();
        $instance = $payment->getMethodInstance();
        $ccNumber = $instance->getInfoInstance()->getCcNumber();
        $use = false;
        $cc_six = substr($ccNumber, 0, 6);

        switch ($payment->getMethod()) {
            case 'authorizenet':
                $use = true;
                $cards_data = array_values($payment->getAdditionalInformation('authorize_cards'));
                $card_data = $cards_data[0];
                $last4 = $card_data['cc_last4'];
                $credit_card_company = $card_data['cc_type'];
                break;

            case 'paypal_direct':
                $use = true;
                $last4 = $payment->getCcLast4();
                $credit_card_company = $payment->getCcType();
                break;

            case 'sagepaydirectpro':
                $use = true;
                $sage = $model->getSagepayInfo();
                $last4 = $sage->getData('last_four_digits');
                $credit_card_company = $sage->getData('card_type');
                break;

            default:
                $last4 = $payment->getCcLast4();
                if ($last4) { $use = true; }
                $credit_card_company = $payment->getCcType();
                break;
        }

        if (strlen($payment->getCcExpMonth()) < 2) {
            $month = "0" . $payment->getCcExpMonth();
        } else {
            $month = $payment->getCcExpMonth();
        }


        $ret = array(
            "include" => $use,
            "type" => 'credit',
            "last4" => $last4,
            "expiration_date" => $month . $payment->getCcExpYear(),
            "status" => "pending"
        );
        if ((is_string($cc_six)) && (strlen($cc_six)==6)) { $ret["bin"] = $cc_six; }

        return $ret;
    }

    public function getVisitorId() {
        $cookie = json_decode($_COOKIE['_kdt'], true);
        return $cookie['i'];
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
                $reccmond = $response['order']['recommendation'];
            } else {
                $reccmond = '';
            }
            $model->setResponse($resp);
            $model->setRecommendation($reccmond);
            $model->setScore($score);
        }
        if ($data != NULL) {
            $model->setRequest($data);
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
