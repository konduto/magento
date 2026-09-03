<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Models\Payment;

class PaymentData extends AbstractData
{
    private $method;
    private $order;

    /**
     * Builds the Konduto "payment" list (array of payment objects, per API doc).
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return Payment[] list of SDK payment models (empty when method is unmapped)
     */
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
            "type" => $type,
            "amount" => (float) $this->helper->treatCents($this->order->getGrandTotal())
        );

        if ($this->method === "credit" || $this->method === "debit") {
            $expirationDate = $this->getCcExpDate($this->payment);
            if ($expirationDate) {
                $data['expiration_date'] = $expirationDate;
            }
            $data['status'] = $this->getCcStatus($this->order);
            if ($this->payment->getCcLast4()) {
                $data['last4'] = $this->payment->getCcLast4();
            }
            $bin = $this->getCcBin($this->payment);
            if ($bin) {
                $data['bin'] = $bin;
            }
        }

        // Return a LIST of payment objects, as required by the Konduto API.
        return array($this->buildPaymentModel($data));
    }

    /**
     * Builds the SDK payment model, registering every field via addField so
     * values like "amount" (and card data on debit) survive serialization —
     * BaseModel::toJsonArray() drops any field not declared in fields().
     *
     * @param array $data
     * @return Payment
     */
    private function buildPaymentModel(array $data)
    {
        $model = Payment::build($data);
        foreach ($data as $field => $value) {
            $model->addField($field, $value);
        }
        return $model;
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