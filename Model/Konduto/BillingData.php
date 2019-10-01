<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Models\Address;

/**
 * Class BillingData
 * @package Konduto\Antifraud\Model\Konduto
 */
class BillingData extends AbstractData
{
    /**
     * @var object
     */
    private $billing;
    public $address;

    /**
     * @param $billing
     * @return Address
     */
    public function getBillingData($billing)
    {
        $this->billing = $billing;
        $this->address = new Address();
        $this->address->setName($billing->getFirstName());
        $this->address->setAddress1($this->getAddressOne());
        if ($this->getAddressTwo()) {
            $this->address->setAddress2($this->getAddressTwo());
        }
        $this->address->setCity($this->getCity());
        $this->address->setState($this->getState());
        $this->address->setZip($this->getZipCode());
        $this->address->setCountry($this->getCountry());

        return $this->address;
    }

    /**
     * @return string
     */
    private function getAddressOne()
    {
        return (string) $this->getStreet() . $this->getNumber();
    }

    /**
     * @return string
     */
    private function getAddressTwo()
    {
        return (string) $this->getComplement() . $this->getNeighborhood();
    }

    /**
     * @return mixed
     */
    private function getStreet()
    {
        $street = $this->helper->getStreet();
        return $this->billing->getStreetLine($street);
    }

    /**
     * @return mixed
     */
    private function getNumber()
    {
        $number = $this->helper->getNumber();
        return $this->billing->getStreetLine($number);
    }

    /**
     * @return mixed
     */
    private function getComplement()
    {
        $complement = $this->helper->getComplement();
        return $this->billing->getStreetLine($complement);
    }

    /**
     * @return mixed
     */
    private function getNeighborhood()
    {
        $district = $this->helper->getNeighborhood();
        return $this->billing->getStreetLine($district);
    }

    /**
     * @return mixed
     */
    private function getCity()
    {
        return $this->billing->getCity();
    }

    /**
     * @return mixed
     */
    private function getState()
    {
        return $this->billing->getRegion();
    }

    /**
     * @return string
     */
    private function getCountry()
    {
        if (!$this->billing->getCountryId()) {
            return 'BR';
        }
        return $this->billing->getCountryId();
    }

    /**
     * @return string|string[]|null
     */
    private function getZipCode()
    {
        return $this->traitZipCode($this->billing->getPostcode());
    }

    /**
     * @param $zipcode
     * @return string|string[]|null
     */
    private function traitZipCode($zipcode)
    {
        return preg_replace('/[^0-9]+/', '', $zipcode);
    }
}
