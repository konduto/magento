<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Core\Konduto;
use Konduto\Models\Customer;

class CustomerData extends AbstractData
{
    private $order;

    public function getCustomerData($order)
    {
        $this->order = $order;
        $customer = $this->helper->getCustomer($order->getCustomerId());
        $customerKonduto = new Customer;
        $customerKonduto->setId($customer->getId());
        $customerKonduto->setName($this->getName($customer->getFirstname()));
        $customerKonduto->setEmail($customer->getEmail());
        $customerKonduto->setDob($customer->getDob());
        $customerKonduto->setTaxId($this->getDocumentNumber($customer));
        $customerKonduto->setCreatedAt($this->getCreatedAt($customer));
        return (object) $customerKonduto;
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

    public function getDocumentNumber($customer)
    {
        $field = $this->helper->getCustomerDocument();
        $document = $customer->getCustomAttribute($field);

        if (!$document) {
            return $this->traitDocument($customer->getTaxvat());
        }

        return $this->traitDocument($document);
    }

    public function getCreatedAt($customer)
    {
        return $this->helper->getDate($customer->getCreatedAt());
    }

    private function traitDocument($document)
    {
        return preg_replace('/[^0-9]+/', '', $document);
    }
}