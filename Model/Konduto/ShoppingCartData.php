<?php

namespace Konduto\Antifraud\Model\Konduto;

class ShoppingCartData
{
    public function getItems($order)
    {
        $itemsArray = array();

        foreach ($order->getAllVisibleItems() as $item) {
            $itemData = array(
                "sku" => (string) $item->getSku(),
                "name" => (string) $item->getName(),
                "unit_cost" => (float) number_format((float) $item->getPrice(), 2, '.', ''),
                "quantity" => (integer) $item->getQtyOrdered()
            );
            if ((float) $item->getDiscountAmount() > 0) {
                $itemData['discount'] = (float) number_format((float) $item->getDiscountAmount(), 2, '.', '');
            }
            array_push($itemsArray, $itemData);
        }
        return $itemsArray;
    }
}