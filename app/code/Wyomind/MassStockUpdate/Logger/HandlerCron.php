<?php

namespace Wyomind\MassStockUpdate\Logger;

class HandlerCron extends \Magento\Framework\Logger\Handler\Base
{
    public $fileName = '/var/log/MassStockUpdate-Cron.log';
    public $loggerType = \Monolog\Logger::NOTICE;
    
}
