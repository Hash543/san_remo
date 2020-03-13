<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\Data\Form\FormKey as FormKey;
use Amasty\Gdpr\Model\Config;

class Settings extends Template
{
    const DOWNLOAD_DATA_BLOCK_SHORT_NAME = 'download';
    const ANONYMISE_DATA_BLOCK_SHORT_NAME = 'anonymise';
    const DELETE_ACCOUT_BLOCK_SHORT_NAME = 'delete';
    const VISIBLE_BLOCK_LAYOUT_VARIABLE_NAME = 'visible_block';
    const NEED_PASSWORD_LAYOUT_VARIABLE_NAME = 'need_password';
    const IS_ORDER_LAYOUT_VARIABLE_NAME = 'is_order';

    /**
     * @var string
     */
    protected $_template = 'settings.phtml';

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var Config
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        \Magento\Framework\Registry $registry,
        FormKey $formKey,
        Config $configProvider,
        array $data = []
    ) {
        parent::__construct($context);
        $this->formKey = $formKey;
        $this->registry = $registry;
        $this->configProvider = $configProvider;
    }

    /**
     * @return array
     */
    public function getPrivacySettings()
    {
        return $this->getAvailableBlocks();
    }

    /**
     * @return array
     */
    private function getPrivacyBlocks()
    {
        $result = [];

        if ($this->configProvider->isModuleEnabled()) {
            if ($this->configProvider->isAllowed(Config::DOWNLOAD)) {
                $result[self::DOWNLOAD_DATA_BLOCK_SHORT_NAME] = [
                    'title' => __('Download personal data'),
                    'content' => __('Here you can download a copy of your personal data which we store for your account in CSV format.'),
                    'hasCheckbox' => false,
                    'checkboxText' => '',
                    'hidePassword' => false,
                    'needPassword' => $this->isNeedPassword(),
                    'submitText' => __('Download'),
                    'action' => $this->getUrl('gdpr/customer/downloadCsv'),
                    'actionCode' => Config::DOWNLOAD
                ];
            }

            if ($this->configProvider->isAllowed(Config::ANONYMIZE)) {
                $result[self::ANONYMISE_DATA_BLOCK_SHORT_NAME] = [
                    'title' => __('Anonymise personal data'),
                    'content' => __('Anonymising your personal data means that it will be replaced with non-personal anonymous information and before that you will get your new login and password to your e-mail address. After this process, your e-mail address and all other personal data will be removed from the website.'),
                    'hasCheckbox' => true,
                    'checkboxText' => __('I agree and I want to proceed'),
                    'hidePassword' => true,
                    'needPassword' => $this->isNeedPassword(),
                    'submitText' => __('Proceed'),
                    'action' => $this->getUrl('gdpr/customer/anonymise'),
                    'actionCode' => Config::ANONYMIZE
                ];
            }

            if ($this->configProvider->isAllowed(Config::DELETE)) {
                $result[self::DELETE_ACCOUT_BLOCK_SHORT_NAME] = [
                    'title' => __('Delete account'),
                    'content' => __('Request to remove your account, together with all your personal data, will be processed by our staff.<br>Deleting your account will remove all the purchase history, discounts, orders, invoices and all other information that might be related to your account or your purchases.<br>All your orders and similar information will be lost.<br>You will not be able to restore access to your account after we approve your removal request.'),
                    'checked' => true,
                    'hasCheckbox' => true,
                    'checkboxText' => __('I understand and I want to delete my account'),
                    'hidePassword' => true,
                    'needPassword' => $this->isNeedPassword(),
                    'submitText' => __('Submit request'),
                    'action' => $this->getUrl('gdpr/customer/addDeleteRequest'),
                    'actionCode' => Config::DELETE
                ];
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    private function getAvailableBlocks()
    {
        $result = [];
        $allBlocks = $this->getPrivacyBlocks();
        $visibleBlocks = $this->getData(self::VISIBLE_BLOCK_LAYOUT_VARIABLE_NAME)
            ? explode(',', $this->getData(self::VISIBLE_BLOCK_LAYOUT_VARIABLE_NAME)) : [];

        if (!$visibleBlocks) {
            return $allBlocks;
        }

        foreach ($visibleBlocks as $blockName) {
            if (array_key_exists($blockName, $allBlocks)) {
                $result[$blockName] = $allBlocks[$blockName];
            }
        }

        return $result;
    }

    /**
     * @return bool
     */
    public function isNeedPassword()
    {
        return $this->getData(self::NEED_PASSWORD_LAYOUT_VARIABLE_NAME);
    }

    /**
     * @return int
     */
    public function isOrder()
    {
        return (int) $this->getData(self::IS_ORDER_LAYOUT_VARIABLE_NAME);
    }

    /**
     * @return string|int
     */
    public function getCurrentOderIncrementId()
    {
        $currentOrder = $this->registry->registry('current_order');

        return $currentOrder ? $currentOrder->getIncrementId() : null;
    }

    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }
}
