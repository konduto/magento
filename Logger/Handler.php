<?php

namespace Konduto\Antifraud\Logger;

use Magento\Framework\Logger\Handler\Base;

class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    public $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    public $fileName = '/var/log/konduto.log';
}
