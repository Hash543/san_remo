<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Block;

use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBar;
use Magento\Framework\View\Element\Template;
use Magento\Cms\Model\Template\Filter as CmsTemplateFilter;

class CookieBar extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var CmsTemplateFilter
     */
    private $cmsTemplateFilter;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlInterface;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        CmsTemplateFilter $cmsTemplateFilter,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->configProvider = $configProvider;
        $this->cmsTemplateFilter = $cmsTemplateFilter;

        // backward compatibility with the Magento 2.1, to avoid compilation issue;
        $this->urlInterface = $context->getUrlBuilder() ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Framework\UrlInterface::class);
    }

    /**
     * @return string
     */
    public function getCookiePolicyText()
    {
        $value = '';

        switch ($this->configProvider->getCookiePrivacyBar()) {
            case CookiePolicyBar::NOTIFICATION:
                $value = $this->configProvider->getNotifyBarText();
                break;
            case CookiePolicyBar::CONFIRMATION:
                $value = $this->configProvider->getConfirmationBarText();
                break;
        }

        if ($value) {
            $value = $this->cmsTemplateFilter->filter($value);
        }

        return \Zend_Json::encode($value);
    }

    /**
     * @return string
     */
    public function getAllowLink()
    {
        return $this->_urlBuilder->getUrl('gdprcookie/cookie/allow');
    }

    /**
     * @return string
     */
    public function getSettingsLink()
    {
        return $this->getUrl($this->configProvider->getCookieSettingsPage());
    }

    /**
     * @return int
     */
    public function getNoticeType()
    {
        return (int)$this->configProvider->getCookiePrivacyBar();
    }

    /**
     * @return int
     */
    public function getWebsiteInteraction()
    {
        $websiteInteraction = (int)$this->configProvider->getCookieWebsiteInteraction();

        if ($websiteInteraction && $this->isAllowedPage($this->urlInterface->getCurrentUrl())) {
            return 0;
        }

        return $websiteInteraction;
    }

    /**
     * @return null|string
     */
    public function getTextBarCollor()
    {
        if (!$this->hasData('text_color_cookies')) {
            $this->setData('text_color_cookies', $this->configProvider->getTextBarCollor());
        }

        return $this->getData('text_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getBackgroundBarCollor()
    {
        if (!$this->hasData('background_color_cookies')) {
            $this->setData('background_color_cookies', $this->configProvider->getBackgroundBarCollor());
        }

        return $this->getData('background_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getButtonsBarCollor()
    {
        if (!$this->hasData('buttons_color_cookies')) {
            $this->setData('buttons_color_cookies', $this->configProvider->getButtonsBarCollor());
        }

        return $this->getData('buttons_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getButtonTextBarCollor()
    {
        if (!$this->hasData('buttons_text_color_cookies')) {
            $this->setData('buttons_text_color_cookies', $this->configProvider->getButtonTextBarCollor());
        }

        return $this->getData('buttons_text_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getLinksBarCollor()
    {
        if (!$this->hasData('link_color_cookies')) {
            $this->setData('link_color_cookies', $this->configProvider->getLinksBarCollor());
        }

        return $this->getData('link_color_cookies');
    }

    /**
     * @return null|string
     */
    public function getBarLocation()
    {
        if (!$this->hasData('cookies_bar_location')) {
            $this->setData('cookies_bar_location', $this->configProvider->getBarLocation());
        }

        return $this->getData('cookies_bar_location');
    }

    /**
     * Convert string to array
     *
     * @param string $string
     * @return array|false
     */
    protected function stringValidationAndConvertToArray($string)
    {
        $validate = function ($urls) {
            return preg_split('|\s*[\r\n]+\s*|', $urls, -1, PREG_SPLIT_NO_EMPTY);
        };

        return $validate($string);
    }

    /**
     * Check if current page is allowed for interaction
     *
     * @param string $currentUrl
     *
     * @return bool
     */
    protected function isAllowedPage($currentUrl)
    {
        $urls = trim($this->configProvider->getAllowedUrls());
        $urls = $urls ? $this->stringValidationAndConvertToArray($urls) : [];

        foreach ($urls as $url) {
            if (false !== strpos($currentUrl, $url)) {
                return true;
            }
        }

        return false;
    }
}
