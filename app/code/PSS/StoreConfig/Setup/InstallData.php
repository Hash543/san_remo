<?php

namespace PSS\StoreConfig\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\ConfigResource\ConfigInterface
     */
    private $resourceConfig;

    /**
     * InstallData constructor.
     * @param LoggerInterface $loggerInterface
     * @param \Magento\Framework\App\Config\ConfigResource\ConfigInterface $resourceConfig
     */
    public function __construct(
        LoggerInterface $loggerInterface,
        \Magento\Framework\App\Config\ConfigResource\ConfigInterface  $resourceConfig)
    {
        $this->logger = $loggerInterface;
        $this->resourceConfig = $resourceConfig;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $this->resourceConfig->saveConfig(
            'general/country/allow',
            'ES',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        $this->resourceConfig->saveConfig(
            'general/country/default',
            'ES',
            \Magento\Framework\App\Config\ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        );

        $setup->endSetup();
    }
}