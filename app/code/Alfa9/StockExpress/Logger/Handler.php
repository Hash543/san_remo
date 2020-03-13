<?php
/**
 *  @author Xavier Sanz <xsanz@alfa9.com>
 *  @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 *  @package Alfa9
 */

namespace Alfa9\StockExpress\Logger;

use Monolog\Logger;


class Handler extends \Magento\Framework\Logger\Handler\Base
{

    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/stockexpress.log';


}