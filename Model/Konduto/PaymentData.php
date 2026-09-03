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

        return $this->getSimplePayment();
    }

    public function getSimplePayment()
    {
        $type = $this->getMethod($this->payment);
        if (!$type) {
            // Unmapped payment method: omit the optional payment list
            // instead of sending an invalid "type" value.
            return array();
        }

        $data = array(
            array(
                "type" => $type,
                "amount" => (float) $this->helper->treatCents($this->order->getGrandTotal())
            )
        );

        if ($this->method === "credit" || $this->method === "debit") {
            $expirationDate = $this->getCcExpDate($this->payment);
            if ($expirationDate) {
                $data[0]['expiration_date'] = $expirationDate;
            }
            $data[0]['status'] = $this->getCcStatus($this->order);
            if ($this->payment->getCcLast4()) {
                $data[0]['last4'] = $this->payment->getCcLast4();
            }
            $bin = $this->getCcBin($this->payment);
            if ($bin) {
                $data[0]['bin'] = $bin;
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
        $paymentSituation = null;
        foreach ($this->transactions as $transaction) {
            $paymentSituation = $transaction['transactionType'];
        }

        if ($paymentSituation === 'capture') {
            return 'approved';
        }

        return 'pending';
    }

    /**
     * Card expiration date in MMYYYY format (Konduto spec).
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return string|false
     */
    private function getCcExpDate($payment)
    {
        if (!$payment->getCcExpMonth() || !$payment->getCcExpYear()) {
            return false;
        }
        return str_pad((string) $payment->getCcExpMonth(), 2, '0', STR_PAD_LEFT)
            . (string) $payment->getCcExpYear();
    }

    /**
     * First six digits of the card (BIN), when available.
     *
     * @param \Magento\Sales\Api\Data\OrderPaymentInterface $payment
     * @return string|false
     */
    private function getCcBin($payment)
    {
        $bin = $payment->getAdditionalInformation('cc_bin')
            ?: $payment->getAdditionalInformation('bin');
        if (!$bin && $payment->getCcNumberEnc() === null && $payment->getData('cc_number')) {
            $bin = substr(preg_replace('/[^0-9]+/', '', $payment->getData('cc_number')), 0, 6);
        }
        return $bin ?: false;
    }
}