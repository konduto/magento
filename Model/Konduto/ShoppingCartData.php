<?php

namespace Konduto\Antifraud\Model\Konduto;

class ShoppingCartData
{
    public function getItems($order)
    {
        $itemsArray = array();

        foreach ($order->getAllItems() as $item) {
            $item = array(
                "sku" => (string) $item->getSku(),
                "name" => (string) $item->getName(),
                "unit_cost" => (float) $item->getPrice(),
                "quantity" => (integer) $item->getQtyOrdered()
            );
            array_push($itemsArray, $item);
        }
        return $itemsArray;
    }
}