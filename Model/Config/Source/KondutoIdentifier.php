<?php

namespace Konduto\Antifraud\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class CustomerAttribute
 * @package Konduto\Antifraud\Model\Config\Source
 */
class KondutoIdentifier implements ArrayInterface
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => 0,
                'label' => __('CustomerId')
            ),
            array(
                'value' =>  1,
                'label' =>__('TaxVat')
            ),
            array(
                'value' =>  2,
                'label' =>__('Email')
            )
        );
    }
}
