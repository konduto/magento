<?php

namespace Konduto\Antifraud\Model\Konduto;

use Konduto\Antifraud\Helper\Data;

/**
 * Class AbstractData
 * @package Konduto\Antifraud\Model\Konduto
 */
abstract class AbstractData
{
    /**
     * @var Data
     */
    public $helper;

    /**
     * AbstractData constructor.
     * @param Data $helper
     */
    public function __construct(Data $helper)
    {
        $this->helper = $helper;
    }
}
