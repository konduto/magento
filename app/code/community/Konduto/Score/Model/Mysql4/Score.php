<?php

class Konduto_Score_Model_Mysql4_Score extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the score_id refers to the key field in your database table.
        $this->_init('score/score', 'score_id');
    }
}