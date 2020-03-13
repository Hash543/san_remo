<?php

namespace Alfa9\StoreInfo\Setup;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;
use Magento\Config\Model\ResourceModel\Config\Data;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;

/**
 * @codeCoverageIgnore
 */
 
class Uninstall implements UninstallInterface
{
    /**
     * @var CollectionFactory
     */
    public $collectionFactory;
    /**
     * @var Data
     */
    public $configResource;
    /**
     * @param CollectionFactory $collectionFactory
     * @param Data $configResource
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Data $configResource
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->configResource    = $configResource;
    }
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.Generic.CodeAnalysis.UnusedFunctionParameter)
     */
    // @codingStandardsIgnoreStart
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    // @codingStandardsIgnoreEnd
    {
        //remove tables
        if ($setup->tableExists('alfa9_storeinfo_store')) {
            $setup->getConnection()->dropTable('alfa9_storeinfo_store');
        }
        //remove config settings if any
        $collection = $this->collectionFactory->create()
            ->addPathFilter('alfa9_storeinfo_store');
        foreach ($collection as $config) {
            $this->deleteConfig($config);
        }
    }
    /**
     * @param AbstractModel $config
     * @throws \Exception
     */
    public function deleteConfig(AbstractModel $config)
    {
        $this->configResource->delete($config);
    }
}
