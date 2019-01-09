<?php

namespace Konduto\Antifraud\Api\Data;

interface QueueInterface
{
    const ORDER_ID = 'order_id';
    const STATUS = 'status';

    /**
     * Get code
     * @return string|null
     */
    public function getOrderId();

    /**
     * Set code
     * @param string $code
     * @return \Konduto\Antifraud\Api\Data\OrderInterface
     */
    public function setOrderId($orderId);

    /**
     * Get status
     * @return string|null
     */
    public function getStatus();

    /**
     * Set status
     * @param string $status
     * @return \Konduto\Antifraud\Api\Data\OrderInterface
     */
    public function setStatus($status);
}
