<?php

namespace Konduto\Antifraud\Model;

use Magento\Framework\Model\AbstractModel;
use Konduto\Antifraud\Model\KondutoService;
use Konduto\Antifraud\Helper\Data;
use Konduto\Antifraud\Model\QueueFactory;
use Konduto\Antifraud\Model\ResourceModel\Queue\CollectionFactory;
use Konduto\Antifraud\Model\QueueRepository;

/**
 * Class QueueManager
 * @package Konduto\Antifraud\Model
 */
class QueueManager extends AbstractModel
{
    /**
     *
     */
    const STATUS_NEW = 'new';

    /**
     * @var \Konduto\Antifraud\Model\KondutoService
     */
    protected $kondutoService;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var \Konduto\Antifraud\Model\QueueFactory
     */
    protected $queueFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var \Konduto\Antifraud\Model\QueueRepository
     */
    protected $queueRepository;

    /**
     * QueueManager constructor.
     * @param \Konduto\Antifraud\Model\KondutoService $kondutoService
     * @param Data $helper
     * @param \Konduto\Antifraud\Model\QueueFactory $queueFactory
     * @param CollectionFactory $collectionFactory
     * @param \Konduto\Antifraud\Model\QueueRepository $queueRepository
     */
    public function __construct(
        KondutoService $kondutoService,
        Data $helper,
        QueueFactory $queueFactory,
        CollectionFactory $collectionFactory,
        QueueRepository $queueRepository
    ) {
        $this->kondutoService = $kondutoService;
        $this->helper = $helper;
        $this->queueFactory = $queueFactory;
        $this->collectionFactory = $collectionFactory;
        $this->queueRepository = $queueRepository;
    }

    /**
     * @param $orderId
     * @throws \Exception
     */
    public function enqueueOrder($order)
    {
        if (!$this->helper->isEnabled()) {
            $this->helper->log('[KONDUTO] ERROR: The module is disabled', 'warning');
            return;
        }

        $paymentsAllowed = $this->helper->getFilterParams();
        $paymentMethod = $order->getPayment()->getMethod();

        if (!in_array($paymentMethod, $paymentsAllowed)) {
            return;
        }

        try {
            $queue = $this->queueFactory->create();
            $queue->setOrderId($order->getId());
            $queue->setStatus(self::STATUS_NEW);
            $this->queueRepository->save($queue);
        } catch (\Exception $e) {
            $this->helper->log('[KONDUTO] ERROR: '. $e->getMessage(), 'error');
        }
    }

    /**
     * @throws \Exception
     */
    public function processOrders()
    {
        if (!$this->helper->isEnabled()) {
            $this->helper->log('The module is disabled', 'warning');
            return;
        }

        if (!$this->helper->apiKeyIsValid()) {
            return;
        }

        $collection = $this->getCollection();

        $this->helper->log('START CRON', 'info');
        foreach ($collection as $order) {
            $this->helper->log('ORDER: ' . $order['order_id'], 'info');
            $this->kondutoService->analysis($order['order_id']);
        }
        $this->helper->log('END CRON', 'info');
    }

    /**
     * @return \Magento\Framework\DataObject[]|\Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
     */
    public function getCollection()
    {
        $collection = $this->collectionFactory->create();
        $collection->setPageSize($this->helper->getMassOrderQuantity());
        return $collection->getItems();
    }
}
