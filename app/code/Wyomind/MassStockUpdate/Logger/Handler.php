<?php

namespace Wyomind\MassStockUpdate\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    public $fileName = '/var/log/MassStockUpdate.log';
    public $loggerType = \Monolog\Logger::NOTICE;
    
    /**
     * Handler constructor
     * @param \Magento\Framework\Filesystem\DriverInterface $filesystem
     * @param string $filePath
     */
    public function __construct(
        \Magento\Framework\Filesystem\DriverInterface $filesystem,
        $filePath = null
    ) {
        parent::__construct($filesystem, $filePath);
        $this->setFormatter(new \Monolog\Formatter\LineFormatter("[%datetime%] %message%\n", null, true));
    }
}
