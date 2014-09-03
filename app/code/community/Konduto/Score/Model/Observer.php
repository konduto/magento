<?php
class Konduto_Score_Model_Observer {

    public function getScore(Varien_Event_Observer $evt) {
        $helper = Mage::helper('score/order');
        $order = $evt->getEvent()->getOrder();
        if (Mage::getStoreConfig("scoreoptions/messages/activate") && Mage::getStoreConfig("scoreoptions/messages/reviewaction") == 3) {
            $response = $helper->getOrderData($order);
        } elseif ((Mage::getStoreConfig("scoreoptions/messages/activate") && Mage::getStoreConfig("scoreoptions/messages/reviewaction") == 2)) {
            $od = $order->getId();
            $helper->saveData(NULL, NULL, $od);
        }
        return;
    }

    public function addManualButton($evt) {
        $order_id = Mage::app()->getFrontController()->getRequest()->getParam('order_id');
        $score_details = Mage::getModel('score/score')->getCollection()->addFieldToFilter('order_no', $order_id)->getFirstItem();

        if (!$score_details->getRequest() && sizeof($score_details) == 1) {
            $block = $evt->getEvent()->getData('block');
            if (Mage::getStoreConfig("scoreoptions/messages/activate") && Mage::getStoreConfig("scoreoptions/messages/reviewaction") == 2) {
                if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_View' && $block->getRequest()->getControllerName() == 'sales_order') {
                    $block->addButton('konduto', array(
                        'label' => Mage::helper('customer')->__('Konduto Analyze'),
                        'onclick' => 'setLocation(\'' . $this->getScoreUrl($block) . '\')',
                        'class' => 'scalable save'
                            ), 0);
                }
            }
        }
    }

    public function addAlertonInvoice($evt) {
        $block = $evt->getEvent()->getData('block');
        if (Mage::getStoreConfig("scoreoptions/messages/activate")) {
            if (get_class($block) == 'Mage_Adminhtml_Block_Sales_Order_View' && $block->getRequest()->getControllerName() == 'sales_order') {
                $ordr_id = $block->getOrder()->getEntityId();
                $collection = Mage::getModel('score/score')->getCollection()
                        ->addFieldToFilter("order_no", $ordr_id);
                if (Mage::getStoreConfig('scoreoptions/messages/reviewupscore') != null && Mage::getStoreConfig('scoreoptions/messages/reviewloscore') != null) {
                    $up_limit = Mage::getStoreConfig('scoreoptions/messages/reviewupscore');
                    $low_limit = Mage::getStoreConfig('scoreoptions/messages/reviewloscore');
                    $score = $collection->getFirstItem()->getScore();
                    if ($score >= $low_limit && $score <= $up_limit) {
                        $recmmondation = 'REVIEW';
                    } elseif ($score > $up_limit) {
                        $recmmondation = 'DECLINE';
                    } else {
                        $recmmondation = 'APPROVE';
                    }
                } else {
                    $recmmondation = $collection->getFirstItem()->getRecommendation();
                }

                if ($recmmondation == 'REVIEW' || $recmmondation == 'DECLINE') {
                    $block->removeButton('order_invoice');
                    $message = Mage::helper('sales')->__('Are you sure you want to Invoice this order? Konduto Recommends to ' . $recmmondation);
                    $block->addButton('order_invoice', array(
                        'label' => Mage::helper('sales')->__('Invoice'),
                        'onclick' => "confirmSetLocation('{$message}','{$block->getInvoiceUrl()}')",
                        'class' => 'go'
                            ), 0);
                }
            }
        }
    }

    public function updateOrder($evt) {
        $order_id = $evt->getEvent()->getOrder()->getId();
        $score_details = Mage::getModel('score/score')->getCollection()->addFieldToFilter('order_no', $order_id)->getFirstItem();
        if (sizeof($score_details) >= 1 && $score_details->getRecommendation() != 'APPROVE') {
            $mode = Mage::getStoreConfig('scoreoptions/messages/mode');
            if ($mode == 1) {
                $private = Mage::getStoreConfig('scoreoptions/messages/productionprikey');
            } else {
                $private = Mage::getStoreConfig('scoreoptions/messages/sandboxprikey');
            }
            $pwd = $private;
            $sslVerify = ($mode == 1 ? true : false);
            $header = array();
            $header[] = 'Content-type: application/json; charset=utf-8';
            $header[] = 'X-Requested-With: Magento';
            $data = '{"status": "approved","comments": "Updated via Magento admin at '.gmdate("r").'"}';
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'https://api.konduto.com/v1/orders/' . $order_id,
                CURLOPT_USERPWD => $pwd,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => $header,
                CURLOPT_CONNECTTIMEOUT => 60,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYHOST => $sslVerify,
                CURLOPT_SSL_VERIFYPEER => $sslVerify
            ));
            $resp = array();
            try {
                $resp = curl_exec($curl);
            } catch (Exception $ex) {
                $resp = 'curlError = ' . curl_error($curl);
            }
            if (Mage::getStoreConfig("scoreoptions/messages/debug")) {
                Mage::log('Order Update Request==>' . $data, NULL, 'konduto.log');
                Mage::log('Order Update Response==>' . $resp, null, 'konduto.log');
            }
        }
    }

    protected function getScoreUrl($block) {
        return $block->getUrl('score/adminhtml_score/getScore');
    }

}