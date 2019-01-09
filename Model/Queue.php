<?php

namespace Konduto\Antifraud\Model;

use Konduto\Antifraud\Api\Data\QueueInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class Queue
 * @package Konduto\Antifraud\Model
 */
class Queue extends AbstractModel implements QueueInterface
{

    /**
     * Queue constructor
     */
    protected function _construct()
    {
        $this->_init('Konduto\Antifraud\Model\ResourceModel\Queue');
    }

    /**
     * Get code
     * @return string
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get status
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * Set code
     * @param string $code
     * @return \Konduto\Antifraud\Api\Data\QueueInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set status
     * @param string $status
     * @return \Konduto\Antifraud\Api\Data\QueueInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}