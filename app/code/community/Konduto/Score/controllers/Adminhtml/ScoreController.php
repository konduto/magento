<?php

class Konduto_Score_Adminhtml_ScoreController extends Mage_Adminhtml_Controller_action {

    protected function _initAction() {
        $this->loadLayout()
                ->_setActiveMenu('score/items')
                ->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));

        return $this;
    }

    public function getScoreAction() {
        $orderId = $this->getRequest()->getParam('order_id');
        $model = Mage::getModel('score/score')->getCollection()->addFieldToFilter('order_no', $orderId);
        if ($model->getFirstItem()->getVisitorId()) {
            $visitor = $model->getFirstItem()->getVisitorId();
            $order = Mage::getModel('sales/order')->load($orderId);
            try {
                $setScore = Mage::helper('score/order')->getOrderData($order, "approved", $visitor);
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('score')->__('Konduto score updated'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('score')->__('Konduto score cannot be updated'));
            }
        } else {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('score')->__('Konduto visitor ID missing'));
        }
        $this->_redirectReferer();
        return;
    }
    
}
