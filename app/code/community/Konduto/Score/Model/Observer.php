<?php
class Konduto_Score_Model_Observer {

    private static $tags = array(
        'catalogsearch_result_index',
        'catalogsearch_advanced_result',
        'customer_account_forgotpassword',
        'customer_account_create',
        'checkout_onepage_index',
        'checkout_cart_index',
        'customer_account_index',
        'catalog_category_default',
        'catalog_product_view',
        'cms_index_index',
        'catalog_category_view',
        'onestepcheckout_index_index'
    );

    public function setTag(Varien_Event_Observer $evt) {
        $handles = $evt->getLayout()->getUpdate()->getHandles();
        $tags = array_intersect($handles, self::$tags);
        $tag = array_pop($tags);
        $evt->getEvent()->getLayout()->getUpdate()->addHandle('konduto_js');
        $evt->getEvent()->getLayout()->getUpdate()->addHandle('konduto_'.$tag);
    }

    public function sendOrderPaymentOk(Varien_Event_Observer $evt) {
      $helper = Mage::helper('score/order');
      $order = $evt->getEvent()->getOrder();
      if (Mage::getStoreConfig("scoreoptions/messages/activate")) {
        $response = $helper->getOrderData($order, 'pending');
      }
    }
    
    public function sendOrderPaymentFail(Varien_Event_Observer $evt) {
      $helper = Mage::helper('score/order');
      $order = $evt->getEvent()->getOrder();
      if (Mage::getStoreConfig("scoreoptions/messages/activate")) {
        $response = $helper->getOrderData($order, 'declined');
      }
    }
    
    public function appendRecommendationColumn(Varien_Event_Observer $evt) {
      $block = $evt->getBlock();
      if (!isset($block)) {
        return $this;
      }
      
      if ($block->getType() == 'adminhtml/sales_order_grid') {
        $block->addColumnAfter(
          'recommendation',
          array(
            'header' => Mage::helper('sales')->__('Recommendation'),
            'index' => 'recommendation',
            'type' => 'text'
          ),
          'status'
        );
      }
    }
    
    public function fillRecommendationColumn(Varien_Event_Observer $evt) {
      $resource = Mage::getSingleton('core/resource');
      $tabel_nam = $resource->getTableName('score/score');
      $collection = $evt->getOrderGridCollection();
      $collection->getSelect()->joinLeft(
        $tabel_nam . ' as score', 'main_table.entity_id = score.order_no'
      );
    }

    protected function getScoreUrl($block) {
        return $block->getUrl('score/adminhtml_score/getScore');
    }

}