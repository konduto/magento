<?php

namespace Konduto\Antifraud\Api;

interface QueueRepositoryInterface
{

    /**
     * Save order
     * @param \Konduto\Antifraud\Api\Data\OrderInterface $order
     * @return \Konduto\Antifraud\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Konduto\Antifraud\Api\Data\QueueInterface $order);

    /**
     * Retrieve order
     * @param string $order_number
     * @return \Konduto\Antifraud\Api\Data\OrderInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($orderId);

    /**
     * Delete order
     * @param \Konduto\Antifraud\Api\Data\OrderInterface $order
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Konduto\Antifraud\Api\Data\QueueInterface $order);

    /**
     * Delete order by Order Number
     * @param string $order_number
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($orderId);
}
