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
        if ($order->getTaxAmount() > 0) {
            $orderKonduto->setTaxAmount((float) $this->treatCents($order->getTaxAmount()));
        }
        $orderKonduto->setCurrency($order->getBaseCurrencyCode());
        $orderKonduto->setInstallments($this->getInstallments($order));
        if ($order->getCreatedAt()) {
            $orderKonduto->setPurchasedAt($this->getPurchasedAt($order));
        }
        $ip = $this->getValidIp($order->getRemoteIp());
        if ($ip) {
            $orderKonduto->setIp($ip);
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

    /**
     * Number of payment installments (Konduto required field, min 1).
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return int
     */
    public function getInstallments($order)
    {
        $installments = 1;
        $payment = $order->getPayment();
        if ($payment) {
            $installments = (int) ($payment->getAdditionalInformation('installments')
                ?: $payment->getAdditionalInformation('cc_installments')
                ?: $payment->getInstallmentCount()
                ?: 1);
        }
        return max(1, $installments);
    }

    /**
     * Order purchase date in ISO 8601 (AAAA-MM-DDTHH:mm:ssZ).
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string
     */
    public function getPurchasedAt($order)
    {
        return date('Y-m-d\TH:i:s\Z', strtotime($order->getCreatedAt()));
    }

    /**
     * Validates IPv4 and IPv6 addresses (both supported by Konduto).
     *
     * @param string|null $ip
     * @return string|false
     */
    public function getValidIp($ip)
    {
        if (!$ip) {
            return false;
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ?: false;
    }
}