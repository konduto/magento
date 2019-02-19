<?php

namespace Konduto\Antifraud\Model\Konduto;

class PaymentData extends AbstractData
{
    private $method;
    private $order;

    public function getPaymentData($order)
    {
        $this->order = $order;
        $this->payment = $this->order->getPayment();
        $this->transactions = $this->helper->getTransactions($this->order->getId());

        if (sizeof($this->transactions) < 2) {
            return $this->getSimplePayment();
        }
    }

    public function getSimplePayment()
    {
        $data = array(
            array(
                "type" => $this->getMethod($this->payment)
            )
        );

        if ($this->method === "credit") {
            $data[0]['expiration_date'] = $this->getCcExpDate($this->payment);
            $data[0]['status'] = $this->getCcStatus($this->order);
            if ($this->payment->getCcLast4()) {
                $data[0]['last4'] = $this->payment->getCcLast4();
            }
        }

        return $data;
    }

    private function getMethod($payment)
    {
        $this->method = $this->helper->getPaymentType($payment->getMethod());
        if (!$this->method) {
            return false;
        }
        return $this->method;
    }

    private function getCcStatus($order)
    {
        foreach ($this->transactions as $transaction) {
            $paymentSituation = $transaction['transactionType'];
        }

        if ($paymentSituation === 'capture') {
            return 'approved';
        }

        return 'pending';
    }

    private function getCcExpDate($payment)
    {
        return (string) $payment->getCcExpMonth() . $payment->getCcExpYear();
    }
}