<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Core\Konduto;
use Konduto\Models\Order;

/**
 * Class OrderData
 * @package Konduto\Antifraud\Model\Konduto
 */
class OrderData extends AbstractData
{
    /**
     * @var
     */
    private $orderData;
    /**
     * @var PaymentData
     */
    private $paymentData;
    /**
     * @var CustomerData
     */
    private $customerData;
    /**
     * @var BillingData
     */
    private $billingData;
    /**
     * @var ShippingData
     */
    private $shippingData;
    /**
     * @var ShoppingCartData
     */
    private $shoppingCartData;

    /**
     * OrderData constructor.
     * @param PaymentData $paymentData
     * @param CustomerData $customerData
     * @param BillingData $billingData
     * @param ShippingData $shippingData
     * @param ShoppingCartData $shoppingCartData
     */
    public function __construct(
        PaymentData $paymentData,
        CustomerData $customerData,
        BillingData $billingData,
        ShippingData $shippingData,
        ShoppingCartData $shoppingCartData
    ){
        $this->paymentData = $paymentData;
        $this->customerData = $customerData;
        $this->billingData = $billingData;
        $this->shippingData = $shippingData;
        $this->shoppingCartData = $shoppingCartData;
    }

    /**
     * @param $order
     * @return object
     */
    public function getOrderData($order)
    {
        $orderKonduto = new Order;
        $orderKonduto->setId($order->getIncrementId());
        if ($order->getVisitorId()) {
            $orderKonduto->setVisitor($order->getVisitorId());
        }
        $orderKonduto->setTotalAmount((float) $this->treatCents($order->getGrandTotal()));
        $orderKonduto->setShippingAmount((float) $this->treatCents($order->getShippingAmount()));
        $orderKonduto->setCurrency($order->getBaseCurrencyCode());
        if(strlen($order->getRemoteIp()) <= 15) {
            $orderKonduto->setIp($order->getRemoteIp());
        }
        $orderKonduto->setCustomer($this->customerData->getCustomerData($order));
        $orderKonduto->setPayment($this->paymentData->getPaymentData($order));
        $orderKonduto->setBilling($this->billingData->getBillingData($order->getBillingAddress()));
        if ($order->getShippingAddress()) {
            $orderKonduto->setShipping($this->shippingData->getShippingData($order->getShippingAddress()));
        }
        $orderKonduto->setShoppingCart($this->shoppingCartData->getItems($order));

        return (object) $orderKonduto;
    }

    public function treatCents($number)
    {
        return number_format($number, 2, '.', '');
    }
}