<?php

namespace Konduto\Antifraud\Model\Config\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * Class CustomerAttribute
 * @package Konduto\Antifraud\Model\Config\Source
 */
class CustomerAttribute implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $attributeCollection;

    /**
     * CustomerAttribute constructor.
     * @param CollectionFactory $attributeCollection
     */
    public function __construct(CollectionFactory $attributeCollection)
    {
        $this->attributeCollection = $attributeCollection;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $collection = $this->attributeCollection->create();
        $collection->setAttributeSetFilter(1);
        $collection->getSelect()->order('frontend_label');

        $result = array();

        foreach ($collection as $child) {
            $result [] = ['value' => $child->getAttributeCode(), 'label' => $child->getFrontendLabel()];
        }

        return $result;
    }
}
