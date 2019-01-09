<?php

namespace Konduto\Antifraud\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class History extends AbstractDb
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('konduto_antifraud_history', 'entity_id');
    }
}
