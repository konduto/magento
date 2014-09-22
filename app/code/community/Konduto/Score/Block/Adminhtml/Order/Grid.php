<?php

class Konduto_Score_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid {

    protected function getTableName() {
        $resource = Mage::getSingleton('core/resource');
        $table = $resource->getTableName('score/score');
        return $table;
    }

    public function setCollection($collection) {
        if (Mage::getStoreConfig("scoreoptions/messages/activate")) {
            $tabel_nam = $this->getTableName();
            $collection->getSelect()->joinLeft(
                    $tabel_nam . ' as b', 'main_table.entity_id = b.order_no'
            );
        }
        $this->_collection = $collection;
    }

    protected function _prepareColumns() {
        if (Mage::getStoreConfig("scoreoptions/messages/activate")) {
            $this->addColumnAfter('recommendation', array(
                'header' => Mage::helper('sales')->__('Recommendation'),
                'index' => 'recommendation',
                    ), 'status');
            return parent::_prepareColumns();
        } else {
            return parent::_prepareColumns();
        }
    }

}