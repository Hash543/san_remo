<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $configWriter;

    /**
     * UpgradeData constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface    $scopeConfig
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->configWriter = $configWriter;
    }

    /**
     * Set new setting value based on old setting value
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface   $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        if ($context->getVersion() && version_compare($context->getVersion(), '1.0.5', '<')) {
            $notificationWebsiteInteraction = $this->scopeConfig
                ->getValue('amasty_gdprcookie/cookie_policy/notification_website_interaction');
            $confirmationWebsiteInteraction = $this->scopeConfig
                ->getValue('amasty_gdprcookie/cookie_policy/confirmation_website_interaction');

            if ($notificationWebsiteInteraction || $confirmationWebsiteInteraction) {
                $this->configWriter->save('amasty_gdprcookie/cookie_policy/website_interaction', 1);
            }

            $this->configWriter->delete('amasty_gdprcookie/cookie_policy/notification_website_interaction');
            $this->configWriter->delete('amasty_gdprcookie/cookie_policy/confirmation_website_interaction');
        }

        $setup->endSetup();
    }
}
