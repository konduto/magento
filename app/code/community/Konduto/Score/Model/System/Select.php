<?php

class Konduto_Score_Model_System_Select extends Mage_Core_Model_Config_Data {

    public function toOptionArray() {
        $opt[] = array('value' => 0, 'label' => Mage::helper('adminhtml')->__("Sandbox"));
        $opt[] = array('value' => 1, 'label' => Mage::helper('adminhtml')->__("Production"));
        return $opt;
    }

}
