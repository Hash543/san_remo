<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Amasty\Gdpr\Model\Config;
use Magento\Customer\Model\Session;

class AccountLinkPlugin
{
    const SORT_ORDER = 175;
    const INSERT_AFTER = 'customer-account-navigation-account-edit-link';

    const M21_LINK_CLASS = 'Magento\Framework\View\Element\Html\Link\Current';
    const M22_LINK_CLASS = 'Magento\Customer\Block\Account\SortLinkInterface';

    const LINK_BLOCK_NAME = 'customer-account-amasty-gdpr-settings';
    const LINK_BLOCK_ALIAS = 'amasty-gdpr-link';

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * AccountLinkPlugin constructor.
     *
     * @param Config $configProvider
     */
    public function __construct(
        Config $configProvider,
        Session $customerSession
    ) {
        $this->configProvider = $configProvider;
        $this->customerSession = $customerSession;
    }

    /**
     * Insert menu item depending on Magento version
     *
     * @param \Magento\Framework\View\Element\Html\Links|\Magento\Customer\Block\Account\Navigation $subject
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeGetLinks($subject)
    {
        if ($subject->getNameInLayout() != 'customer_account_navigation'
            || !$this->configProvider->isModuleEnabled()
            || !$this->customerSession->isLoggedIn()
        ) {
            return;
        }

        $linkClass = interface_exists(self::M22_LINK_CLASS) ? self::M22_LINK_CLASS : self::M21_LINK_CLASS;

        if (!$subject->getLayout()->hasElement(self::LINK_BLOCK_NAME)) {
            $subject->getLayout()->createBlock(
                $linkClass,
                self::LINK_BLOCK_NAME,
                [
                    'data' => [
                        'path' => 'gdpr/customer/settings',
                        'label' => __('Privacy Settings'),
                        'sortOrder' => self::SORT_ORDER
                    ]
                ]
            );
        }

        if (!$subject->getChildBlock(self::LINK_BLOCK_ALIAS)) {
            $subject->insert(
                $subject->getLayout()->getBlock(self::LINK_BLOCK_NAME),
                self::INSERT_AFTER,
                true,
                self::LINK_BLOCK_ALIAS
            );
        }
    }
}
