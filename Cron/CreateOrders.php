<?php

namespace Konduto\Antifraud\Cron;

class CreateOrders
{
    public $queueManager;

    /**
     * CreateOrders constructor.
     * @param \Konduto\Antifraud\Model\QueueManager $queueManager
     */
    public function __construct(\Konduto\Antifraud\Model\QueueManager $queueManager)
    {
        $this->queueManager = $queueManager;
    }

    /**
     * @throws \Exception
     */
    public function execute()
    {
        $this->queueManager->processOrders();
    }
}
