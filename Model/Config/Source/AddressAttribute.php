<?php

namespace Konduto\Antifraud\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class AddressAttribute
 * @package Konduto\Antifraud\Model\Config\Source
 */
class AddressAttribute implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => __('Street 1')
            ),
            array(
                'value' =>  1,
                'label' =>__('Street 2')
            ),
            array(
                'value' =>  2,
                'label' =>__('Street 3')
            ),
            array(
                'value' =>  3,
                'label' =>__('Street 4')
            )
        );
    }
}
