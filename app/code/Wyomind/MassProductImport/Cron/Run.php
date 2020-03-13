<?php

/* *
 * Copyright © 2016 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Cron;

class Run extends \Wyomind\MassStockUpdate\Cron\Run
{

    public $module = "massproductimport";

    public function __construct(
        \Wyomind\MassProductImport\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Wyomind\MassStockUpdate\Logger\LoggerCron $logger,
        \Wyomind\Core\Helper\Data $coreHelper
    ) {
    
        $this->_collectionFactory = $collectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreDate = $coreDate;
        $this->_logger = $logger;
        $this->_transportBuilder = $transportBuilder;
        $this->_coreHelper = $coreHelper;
    }
}
