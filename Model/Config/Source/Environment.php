<?php

namespace Konduto\Antifraud\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class AddressAttribute
 * @package Konduto\Antifraud\Model\Config\Source
 */
class Environment implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => __('Sandbox')
            ),
            array(
                'value' =>  1,
                'label' =>__('Production')
            )
        );
    }
}
