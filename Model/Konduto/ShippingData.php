<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Core\Konduto;
use Konduto\Models\Address;

class ShippingData extends AbstractData
{
    private $shipping;

    public function getShippingData($shipping)
    {
        $this->shipping = $shipping;
        $shippingKonduto = new Address;
        $shippingKonduto->setName($shipping->getFirstName());
        $shippingKonduto->setAddress1($this->getAddressOne());
        if ($this->getAddressTwo()) {
            $shippingKonduto->setAddress2($this->getAddressTwo());
        }
        $shippingKonduto->setCity($this->getCity());
        $shippingKonduto->setState($this->getState());
        $shippingKonduto->setZip($this->getZipCode());
        $shippingKonduto->setCountry($this->getCountry());

        return $shippingKonduto;
    }

    private function getAddressOne()
    {
        return (string) $this->getStreet($this->shipping) . $this->getNumber($this->shipping);
    }

    private function getAddressTwo()
    {
        return (string) $this->getComplement($this->shipping) . $this->getNeighborhood($this->shipping);
    }

    private function getStreet()
    {
        $street = $this->helper->getStreet();
        return $this->shipping->getStreetLine($street);
    }

    private function getNumber()
    {
        $number = $this->helper->getNumber();
        return $this->shipping->getStreetLine($number);
    }

    private function getComplement()
    {
        $complement = $this->helper->getComplement();
        return $this->shipping->getStreetLine($complement);
    }

    private function getNeighborhood()
    {
        $district = $this->helper->getNeighborhood();
        return $this->shipping->getStreetLine($district);
    }

    private function getCity()
    {
        return $this->shipping->getCity();
    }

    private function getState()
    {
        return $this->shipping->getRegion();
    }

    private function getCountry()
    {
        if (!$this->shipping->getCountryId()) {
            return 'BR';
        }
        return $this->shipping->getCountryId();
    }

    private function getZipCode()
    {
        return $this->traitZipCode($this->shipping->getPostcode());
    }

    private function traitZipCode($zipcode)
    {
        return preg_replace('/[^0-9]+/', '', $zipcode);
    }
}