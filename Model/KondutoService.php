<?php

namespace Konduto\Antifraud\Model;

use Konduto\Antifraud\Helper\Data;
use Konduto\Antifraud\Model\Konduto\OrderData;
use Konduto\Antifraud\Model\ResourceModel\Queue\CollectionFactory;
use Konduto\Core\Konduto;
use Konduto\Exceptions\KondutoException;
use Magento\Framework\App\State;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\ResourceModel\Attribute as OrderAttributeResource;
use Magento\Sales\Model\Service\InvoiceService;

/**
 * Class KondutoService
 * @package Konduto\Antifraud\Model
 */
class KondutoService extends AbstractModel
{
    /**
     * It is used if there is a problem with communication with Konduto
     */
    const STATUS_RETRY = 'retry';

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;
    /**
     * @var QueueRepository
     */
    protected $queueRepository;
    /**
     * @var HistoryRepository
     */
    protected $historyRepository;
    /**
     * @var QueueFactory
     */
    protected $queueFactory;
    /**
     * @var HistoryFactory
     */
    protected $historyFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    /**
     * @var Data
     */
    protected $helper;
    /**
     * @var OrderData
     */
    protected $orderData;
    /**
     * @var
     */
    protected $orderId;
    /**
     * @var
     */
    private $response;
    /**
     * @var
     */
    private $order;
    /**
     * @var State
     */
    private $state;
    /**
     * @var OrderAttributeResource
     */
    private $orderAttribute;
    /**
     * @var Order
     */
    private $orderModel;
    /**
     * @var InvoiceService
     */
    private $invoiceService;
    /**
     * @var Transaction
     */
    private $transaction;
    /**
     * @var InvoiceSender
     */
    private $invoiceSender;

    /**
     * KondutoService constructor.
     * @param OrderRepositoryInterface $orderRepository
     * @param QueueRepository $queueRepository
     * @param HistoryRepository $historyRepository
     * @param QueueFactory $queueFactory
     * @param HistoryFactory $historyFactory
     * @param CollectionFactory $collectionFactory
     * @param Data $helper
     * @param OrderData $orderData
     * @param State $state
     * @param OrderAttributeResource $orderAttribute
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        QueueRepository $queueRepository,
        HistoryRepository $historyRepository,
        QueueFactory $queueFactory,
        HistoryFactory $historyFactory,
        CollectionFactory $collectionFactory,
        Data $helper,
        OrderData $orderData,
        State $state,
        OrderAttributeResource $orderAttribute,
        Order $orderModel,
        InvoiceService $invoiceService,
        Transaction $transaction,
        InvoiceSender $invoiceSender
    ) {
        $this->helper = $helper;
        $this->queueFactory = $queueFactory;
        $this->queueRepository = $queueRepository;
        $this->historyFactory = $historyFactory;
        $this->historyRepository = $historyRepository;
        $this->collectionFactory = $collectionFactory;
        $this->orderRepository = $orderRepository;
        $this->orderData = $orderData;
        $this->state = $state;
        $this->orderAttribute = $orderAttribute;
        $this->orderModel = $orderModel;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Method for sending orders and returning status.
     *
     * @param $orderId
     */
    public function analysis($orderId)
    {
        $this->orderId = $orderId;
        $this->order = $this->helper->loadOrder($this->orderId);
        $orderKonduto = $this->orderData->getOrderData($this->order);
        try {
            $response = Konduto::analyze($orderKonduto);
            $this->registerHistory($response);
            // emulate adminhtml
            $this->state->emulateAreaCode('adminhtml', [$this, 'registerStatus'], [$response]);
            $this->registerStatus($response);
            $this->queueRepository->deleteById($this->orderId);
        } catch (KondutoException $exception) {
            $this->queueRetry();
            $this->helper->log('INCREMENT ID : ' . $this->order->getIncrementId() . ' - ' . $exception, 'info');
        }
    }

    /**
     * Method to update order with payment status.
     * @param $orderId
     * @param $newStatus
     * @return array
     * @throws KondutoException
     */
    public function updateOrderStatus($orderId, $newStatus)
    {
        if (!in_array($newStatus, $this->helper::$updateOrderStatusList)) {
            throw new \InvalidArgumentException(sprintf('Invalid new status code (%s)', $newStatus));
        }

        try {
            $this->helper->apiKeyIsValid();
            $kondutoResponse = Konduto::updateOrderStatus($orderId, $newStatus, '');
        } catch (Exception $exception) {
            throw $exception;
        }

        return $kondutoResponse;
    }

    /**
     * @param $params
     * @return bool
     * @throws KondutoException
     */
    public function updateOrder($params)
    {
        $isValidSignature = $this->helper->validateSignature($params);

        if ($isValidSignature) {
            $kondutoResponse = $this->updateOrderStatus($params['order_id'], $params['status']);
        }

        if (!isset($kondutoResponse) || $kondutoResponse === false) {
            return false;
        }

        $order = $this->orderModel->loadByIncrementId($params['order_id']);
        if (!$order) {
            return false;
        }
        $this->updateHistory($order->getId(), $params['status']);
        $order->setKondutoStatus($params['status']);
        if ($this->helper->getAutomaticKondutoUpdate()) {
            $order->setStatus($this->helper->getStatusCode($params['status']));
            if ($params['status'] = 'approved') {
                $this->createInvoice($order);
            }
        }

        $order->save();
        return true;
    }

    /**
     * Method that updates the order on the history entity.
     * @param $orderId
     * @param $newStatus
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateHistory($orderId, $newStatus)
    {
        $nowTime = $this->helper->getNowDateTime();
        $history = $this->historyRepository->getById($orderId);
        $history->setStatus($newStatus);
        $history->setUpdatedAt($nowTime);
        $this->historyRepository->save($history);
    }

    /**
     * @param $response
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function registerHistory($response)
    {
        $nowTime = $this->helper->getNowDateTime();
        $history = $this->historyFactory->create();
        $history->setOrderId($this->orderId);
        $history->setStatus($response->getStatus());
        $history->setCreatedAt($nowTime);
        $history->setUpdatedAt($nowTime);
        $this->historyRepository->save($history);
    }

    /**
     * @param $response
     * @throws \Exception
     */
    public function registerStatus($response)
    {
        $this->order->setKondutoStatus($response->getStatus());
        $this->orderAttribute->saveAttribute($this->order, 'konduto_status');
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function queueRetry()
    {
        $queue = $this->queueRepository->getById($this->orderId);
        $queue->setStatus(self::STATUS_RETRY);
        $this->queueRepository->save($queue);
    }

    /**
     * @param $order
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createInvoice($order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->invoiceService->prepareInvoice($order);
            $invoice->register();
            $invoice->save();
            $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
                ->setIsCustomerNotified(true)
                ->save();
        }
    }
}
