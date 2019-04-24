<?php

namespace Konduto\Antifraud\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Payment\Helper\Data;

/**
 * Class PaymentMethods
 * @package Konduto\Antifraud\Model\Config\Source
 */
class PaymentMethods implements ArrayInterface
{
    /**
     * @var Data
     */
    public $helper;

    /**
     * PaymentMethods constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $data = $this->helper->getPaymentMethods();

        $result = array();

        foreach ($data as $code => $child) {
            if (!array_key_exists('title', $child)) {
                continue;
            }

            $result [] = ['value' => $code, 'label' => $child['title'] ];
        }

        return $result;
    }
}
