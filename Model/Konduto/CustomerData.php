<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Core\Konduto;
use Konduto\Models\Customer;

class CustomerData extends AbstractData
{
    private $order;
    public $customer;

    public function getCustomerData($order)
    {
        $this->order = $order;

        if ($order->getCustomerIsGuest()) {
            $customerKonduto = new Customer;
            $customerKonduto->setId($order->getCustomerEmail());
            $customerKonduto->setName(
                $order->getBillingAddress()->getFirstName() . ' ' . $order->getBillingAddress()->getLastname()
            );
            $customerKonduto->setEmail($order->getCustomerEmail());
            $customerKonduto->setNew(true);
            if ($order->getBillingAddress()->getTelephone()) {
                $customerKonduto->setPhone1($order->getBillingAddress()->getTelephone());
            }
            $taxId = $this->getGuestTaxId($order);
            if ($taxId) {
                $customerKonduto->setTaxId($taxId);
            }
            if ($order->getCustomerDob()) {
                $customerKonduto->setDob($this->helper->getDate($order->getCustomerDob()));
            }
            return $customerKonduto;
        }
        $this->customer = $this->helper->getCustomer($order->getCustomerId());
        $customerKonduto = new Customer;
        $customerKonduto->setId($this->getKondutoIdentifier());
        $customerKonduto->setName($this->getName($this->customer->getFirstname() . ' ' . $this->customer->getLastname()));
        $customerKonduto->setEmail($this->customer->getEmail());
        if ($this->customer->getDob()) {
            $customerKonduto->setDob($this->helper->getDate($this->customer->getDob()));
        }
        $taxId = $this->helper->getDocumentNumber($this->customer);
        if ($taxId) {
            $customerKonduto->setTaxId($taxId);
        }
        $customerKonduto->setCreatedAt($this->getCreatedAt($this->customer));

        if ($order->getBillingAddress()->getTelephone()) {
            $customerKonduto->setPhone1($order->getBillingAddress()->getTelephone());
        }

        return (object) $customerKonduto;
    }

    public function getKondutoIdentifier()
    {
        $identifier = $this->helper->getKondutoIdentifierData($this->customer);
        if (!$identifier) {
            $identifier = $this->customer->getId();
        }
        return $identifier;
    }
    
    public function getName($name)
    {
        if (!$name) {
            return false;
        }
        return (string) trim($name);
    }

    public function getEmail($email)
    {
        if (!$email) {
            $email = $this->order->getCustomerEmail();
        }

        return (string) trim($email);
    }

    public function getBirthDate($birthDate)
    {
        if (!$birthDate) {
            $birthDate = $this->order->getCustomerDob();
        }

        return (string) $birthDate;
    }

    public function getCreatedAt($customer)
    {
        return $this->helper->getDate($customer->getCreatedAt());
    }

    /**
     * Tax document (CPF/CNPJ) for guest checkouts, from order/billing VAT fields.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @return string|false
     */
    public function getGuestTaxId($order)
    {
        $document = $order->getCustomerTaxvat()
            ?: $order->getBillingAddress()->getVatId();
        if (!$document) {
            return false;
        }
        return $this->helper->traitDocument($document);
    }
}