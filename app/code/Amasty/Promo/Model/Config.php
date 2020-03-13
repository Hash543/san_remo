<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Promo
 */


namespace Amasty\Promo\Model;

class Config
{
    const PROMO_SECTION = 'ampromo/';
    const GROUP_PROMO_MESSAGES = 'messages/';
    const GENERAL_GROUP = 'general/';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $config;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param $group
     * @param $path
     *
     * @return mixed
     */
    private function getScopeValue($group, $path)
    {
        return $this->config->getValue(
            self::PROMO_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $group
     * @param $path
     *
     * @return bool
     */
    private function isSetFlag($group, $path)
    {
        return $this->config->isSetFlag(
            self::PROMO_SECTION . $group . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getSelectionMethod()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'gift_selection_method');
    }

    /**
     * @return mixed
     */
    public function getGiftsCounter()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'display_remaining_gifts_counter');
    }

    /**
     * @return mixed
     */
    public function getAddMessage()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'add_message');
    }

    /**
     * @return bool
     */
    public function isAutoOpenPopup()
    {
        return $this->isSetFlag(self::GROUP_PROMO_MESSAGES, 'auto_open_popup');
    }

    /**
     * @return mixed
     */
    public function getAutoAddType()
    {
        return $this->getScopeValue(self::GENERAL_GROUP, 'auto_add');
    }

    /**
     * @return mixed
     */
    public function getPopupName()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'popup_title');
    }

    /**
     * @return mixed
     */
    public function getAddButtonName()
    {
        return $this->getScopeValue(self::GROUP_PROMO_MESSAGES, 'add_button_title');
    }
}
