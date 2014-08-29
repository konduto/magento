<?php

class Konduto_Score_Block_Product_View extends Mage_Catalog_Block_Product_View {

    protected function _prepareLayout() {

        parent::_prepareLayout();
        $headBlock = $this->getLayout()->getBlock('head');
        if ($headBlock) {
            $product = $this->getProduct();
            if (!$headBlock->getChild('meta_kdt')) {
                $text = $this->getLayout()->createBlock('core/text', 'meta_kdt');
                $text->setText('<meta property="kdt:product1" content="sku = ' . $product->getSku() . ', name='.$product->getName().'"/>');
                $headBlock->append($text);
            }
        }
        return $this;
    }

}
