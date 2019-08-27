<?php

namespace Konduto\Antifraud\Plugin\Order;

use Konduto\Antifraud\Model\QueueManager;
use Konduto\Antifraud\Helper\Data;
use Magento\Sales\Api\OrderManagementInterface;

/**
 * Class PlaceAfterPlugin
 * @package Konduto\Antifraud\Plugin\Order
 */
class PlaceAfterPlugin
{

    /**
     * PlaceAfterPlugin constructor.
     * @param QueueManager $queueManager
     */
    public function __construct(QueueManager $queueManager, Data $helper)
    {
        $this->queueManager = $queueManager;
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Sales\Api\OrderManagementInterface $orderManagementInterface
     * @param \Magento\Sales\Model\Order\Interceptor $order
     * @return $order
     */
    public function afterPlace(OrderManagementInterface $orderManagementInterface , $order)
    {
        if (!$this->helper->isEnabled()) {
            return $order;
        }
        $this->queueManager->enqueueOrder($order);
        $sessionId = $this->getVisitorId();
        if ($sessionId) {
            $order->setVisitorId($sessionId);
            $order->save();
        }
        return $order;
    }

    /**
     * @return mixed
     */
    public function getVisitorId()
    {
        $id = null;
        if (isset($_COOKIE['_kdt'])) {
            $cookie = json_decode($_COOKIE['_kdt'], true);
            $id = $cookie['i'];
        }
        return $id;
    }
}

