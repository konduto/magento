<?php

class Konduto_Score_Model_System_Save extends Mage_Core_Model_Config_Data {

    public function save() {
        $group = $this->getData('groups');
        $fields = $group['messages']['fields'];
        if ($fields['activate']['value'] == 1) {
            if ($fields['mode']['value'] == 1) {
                $productionpublickey = $fields['productionpublickey']['value'];
                $productionprikey = $fields['productionprikey']['value'];
                if ($productionpublickey == NULL || $productionpublickey == ' ' || !(isset($productionpublickey))) {
                    Mage::throwException(Mage::helper('core')->__('Production Public key is required'));
                    die;
                }
                if ($productionprikey == NULL || $productionprikey == ' ' || !(isset($productionprikey))) {
                    Mage::throwException(Mage::helper('core')->__('Production Private key is required'));
                    die;
                }
                if (strlen($productionpublickey)!=11) {
                    Mage::throwException(Mage::helper('core')->__('Public key lenth must be 11 characters'));
                    die;
                }       
                if (strlen($productionprikey)!=21) {
                    Mage::throwException(Mage::helper('core')->__('Private key length must be 21 characters'));
                    die;
                }
                if ((substr($productionprikey,0,1)!="P") || (substr($productionpublickey,0,1)!="P")) {
                    Mage::throwException(Mage::helper('core')->__('Please enter a valid Production key'));
                    die;
                }
            }
            elseif ($fields['mode']['value'] == 0) {
                $sandboxpublickey = $fields['sandboxpublickey']['value'];
                $sandboxprikey = $fields['sandboxprikey']['value'];
                if ($sandboxpublickey == NULL || $sandboxpublickey == ' ' || !(isset($sandboxpublickey))) {
                    Mage::throwException(Mage::helper('core')->__('Sandbox Public key is required'));
                    die;
                }
                if ($sandboxprikey == NULL || $sandboxprikey == ' ' || !(isset($sandboxprikey))) {
                    Mage::throwException(Mage::helper('core')->__('Sandbox Private key is required'));
                    die;
                }
                if (strlen($sandboxpublickey)!=11) {
                    Mage::throwException(Mage::helper('core')->__('Public key lenth must be 11 characters'));
                    die;
                }
                if (strlen($sandboxprikey)!=21) {
                    Mage::throwException(Mage::helper('core')->__('Private key length must be 21 characters'));
                    die;
                }
                if ((substr($sandboxprikey,0,1)!="T") || (substr($sandboxpublickey,0,1)!="T")) {
                    Mage::throwException(Mage::helper('core')->__('Please enter a valid Sandbox key'));
                    die;
                }
            }
        }
        return parent::save();
    }

}
