<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Block;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBar;
use Magento\Framework\App\DefaultPathInterface;
use Magento\Framework\View\Element\Template\Context;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Context $context,
        ConfigProvider $configProvider,
        DefaultPathInterface $defaultPath,
        array $data = []
    ) {
        parent::__construct($context, $defaultPath, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if ($this->configProvider->getCookiePrivacyBar() !== CookiePolicyBar::CONFIRMATION) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getPath()
    {
        if (!$this->hasData('path')) {
            $this->setData('path', $this->configProvider->getCookieSettingsPage());
        }

        return $this->getData('path');
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return __('Cookie Settings');
    }
}
