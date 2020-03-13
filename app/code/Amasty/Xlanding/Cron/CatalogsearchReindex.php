<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Xlanding
 */


namespace Amasty\Xlanding\Cron;

use Psr\Log\LoggerInterface;
use Magento\Indexer\Model\Indexer;
use Magento\Framework\Indexer\StateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\CatalogSearch\Model\ResourceModel\EngineProvider;

class CatalogsearchReindex
{
    const INDEXER_ID = 'catalogsearch_fulltext';
    const REGISTRY_REPLACE_ENGINE = 'amxlanding_replace_engine';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Indexer
     */
    private $indexer;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Registry $coreRegistry,
        LoggerInterface $logger,
        Indexer $indexer,
        ScopeConfigInterface $config
    ) {
        $this->moduleManager = $moduleManager;
        $this->coreRegistry = $coreRegistry;
        $this->logger = $logger;
        $this->indexer = $indexer;
        $this->config = $config;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if ($this->checkEngine()) {
            return;
        }

        try {
            $this->indexer->load(self::INDEXER_ID);
            if ($this->indexer->getState()->getStatus() == StateInterface::STATUS_WORKING) {
                throw new \Zend_Exception('Status Working');
            }

            $this->coreRegistry->register(self::REGISTRY_REPLACE_ENGINE, true);
            $this->indexer->reindexAll();
            $this->coreRegistry->unregister(self::REGISTRY_REPLACE_ENGINE);
        } catch (\Exception $e) {
            $this->logError($e);
        }
    }

    /**
     * @return bool
     */
    private function checkEngine()
    {
        if ($this->config->getValue(EngineProvider::CONFIG_ENGINE_PATH) == 'mysql') {
            return true;
        }

        return false;
    }

    /**
     * @param \Exception $e
     */
    private function logError(\Exception $e)
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/amasty_landing_reindex_error.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($e->getMessage());
    }
}
