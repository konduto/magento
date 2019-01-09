<?php

namespace Konduto\Antifraud\Api\Data;

interface HistoryInterface
{
    const ORDER_ID = 'order_id';
    const STATUS = 'status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

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

    /**
     * Gets the created-at timestamp for the order.
     *
     * @return string|null Created-at timestamp.
     */
    public function getCreatedAt();

    /**
     * Sets the created-at timestamp for the order.
     *
     * @param string $createdAt timestamp
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Gets the updated-at timestamp for the order.
     *
     * @return string|null Updated-at timestamp.
     */
    public function getUpdatedAt();

    /**
     * Sets the updated-at timestamp for the order.
     *
     * @param string $timestamp
     * @return $this
     */
    public function setUpdatedAt($timestamp);
}
