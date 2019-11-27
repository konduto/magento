<?php

namespace Konduto\Antifraud\Model;

use Konduto\Antifraud\Api\Data\HistoryInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class History
 * @package Konduto\Antifraud\Model
 */
class History extends AbstractModel implements HistoryInterface
{

    /**
     * History constructor
     */
    protected function _construct()
    {
        $this->_init('Konduto\Antifraud\Model\ResourceModel\History');
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
     * Returns created_at
     *
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Returns updated_at
     *
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
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

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
    * {@inheritdoc}
    */
    public function setUpdatedAt($timestamp)
    {
        return $this->setData(self::UPDATED_AT, $timestamp);
    }
}