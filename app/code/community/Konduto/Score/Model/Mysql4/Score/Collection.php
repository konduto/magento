<?php

class Konduto_Score_Model_Mysql4_Score_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('score/score');
    }
}