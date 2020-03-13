<?php
/**
 * @author Ignacio Martinez
 */
namespace PSS\Base\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base {
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = \Monolog\Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/pss.log';
}