<?php

namespace Konduto\Antifraud\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

use Konduto\Antifraud\Model\QueueFactory;
use Konduto\Antifraud\Api\QueueRepositoryInterface;
use Konduto\Antifraud\Api\Data\QueueInterface;
use Konduto\Antifraud\Model\ResourceModel\Queue as QueueResource;
use Konduto\Antifraud\Model\ResourceModel\Queue\CollectionFactory as QueueCollectionFactory;

/**
 * Class QueueRepository
 * @package Konduto\Antifraud\Model
 */
class QueueRepository implements QueueRepositoryInterface
{
    /**
     * @var QueueResource
     */
    protected $resource;
    /**
     * @var QueueCollectionFactory
     */
    protected $collection;
    /**
     * @var \Konduto\Antifraud\Model\QueueFactory
     */
    protected $queueFactory;

    /**
     * QueueRepository constructor.
     * @param QueueResource $resource
     * @param QueueCollectionFactory $queueCollectionFactory
     * @param \Konduto\Antifraud\Model\QueueFactory $queueFactory
     */
    public function __construct(
        QueueResource $resource,
        QueueCollectionFactory $queueCollectionFactory,
        QueueFactory $queueFactory
    ) {
        $this->resource = $resource;
        $this->collection = $queueCollectionFactory;
        $this->queueFactory = $queueFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(QueueInterface $order)
    {
        try {
            $this->resource->save($order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the sql: %1',
                $exception->getMessage()
            ));
        }

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function getById($orderId)
    {
        $order = $this->queueFactory->create();
        $order->load($orderId, 'order_id');

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order with id "%1" does not exist.', $order->getId()));
        }

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QueueInterface $order)
    {
        try {
            $this->resource->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the order: %1',
                $exception->getMessage()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($orderId)
    {
        return $this->delete($this->getById($orderId));
    }
}
