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
            $customerKonduto->setName($order->getBillingAddress()->getFirstName());
            $customerKonduto->setEmail($order->getCustomerEmail());
            return $customerKonduto;
        }
        $this->customer = $this->helper->getCustomer($order->getCustomerId());
        $customerKonduto = new Customer;
        $customerKonduto->setId($this->getKondutoIdentifier());
        $customerKonduto->setName($this->getName($this->customer->getFirstname()));
        $customerKonduto->setEmail($this->customer->getEmail());
        $customerKonduto->setDob($this->customer->getDob());
        $customerKonduto->setTaxId($this->helper->getDocumentNumber($this->customer));
        $customerKonduto->setCreatedAt($this->getCreatedAt($this->customer));
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
}
