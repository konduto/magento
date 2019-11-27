<?php

namespace Konduto\Antifraud\Model;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

use Konduto\Antifraud\Api\HistoryRepositoryInterface;
use Konduto\Antifraud\Api\Data\HistoryInterface;
use Konduto\Antifraud\Model\ResourceModel\History as HistoryResource;
use Konduto\Antifraud\Model\ResourceModel\History\CollectionFactory as HistoryCollectionFactory;
use Konduto\Antifraud\Model\HistoryFactory;

/**
 * Class HistoryRepository
 * @package Konduto\Antifraud\Model
 */
class HistoryRepository implements HistoryRepositoryInterface
{
    /**
     * @var HistoryResource
     */
    protected $resource;
    /**
     * @var HistoryCollectionFactory
     */
    protected $collection;
    /**
     * @var \Konduto\Antifraud\Model\HistoryFactory
     */
    protected $historyFactory;

    /**
     * HistoryRepository constructor.
     * @param HistoryResource $resource
     * @param HistoryCollectionFactory $queueCollectionFactory
     * @param \Konduto\Antifraud\Model\HistoryFactory $historyFactory
     */
    public function __construct(
        HistoryResource $resource,
        HistoryCollectionFactory $queueCollectionFactory,
        HistoryFactory $historyFactory
    ) {
        $this->resource = $resource;
        $this->collection = $queueCollectionFactory;
        $this->historyFactory = $historyFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function save(HistoryInterface $order)
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
        $order = $this->historyFactory->create();
        $order->load($orderId, 'order_id');

        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Order with id "%1" does not exist.', $order->getId()));
        }

        return $order;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(HistoryInterface $order)
    {
        try {
            $this->resource->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Sql: %1',
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
