<?php

class Konduto_Score_Model_System_Save extends Mage_Core_Model_Config_Data {

    public function save() {
        $group = $this->getData('groups');
        $fields = $group['messages']['fields'];
        if ($fields['activate']['value'] == 1) {
            if ($fields['mode']['value'] == 1) {
                $productionpublickey = $fields['productionpublickey']['value'];
                $productionprikey = $fields['productionprikey']['value'];
                if ($productionprikey == NULL || $productionprikey == ' ' || !(isset($productionprikey))) {
                    Mage::throwException(Mage::helper('core')->__('Production private key is required'));
                    die;
                }
                if ($productionpublickey == NULL || $productionpublickey == ' ' || !(isset($productionpublickey))) {
                    Mage::throwException(Mage::helper('core')->__('Production public key is required'));
                    die;
                }
            } elseif ($fields['mode']['value'] == 0) {
                $sandboxpublickey = $fields['sandboxpublickey']['value'];
                $sandboxprikey = $fields['sandboxprikey']['value'];
                if ($sandboxprikey == NULL || $sandboxprikey == ' ' || !(isset($sandboxprikey))) {
                    Mage::throwException(Mage::helper('core')->__('Sandbox private key is required'));
                    die;
                }
                if ($sandboxpublickey == NULL || $sandboxpublickey == ' ' || !(isset($sandboxpublickey))) {
                    Mage::throwException(Mage::helper('core')->__('Sandbox public key is required'));
                    die;
                }
            }
        }
        return parent::save();
    }

}
