<?php

class Konduto_Score_Model_System_Review extends Mage_Core_Model_Config_Data {

    public function toOptionArray() {
        $opt[] = array('value' => 3, 'label' => Mage::helper('adminhtml')->__("Automatic"));
        return $opt;
    }

}
