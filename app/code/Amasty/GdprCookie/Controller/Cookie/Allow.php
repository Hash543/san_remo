<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Controller\Cookie;

use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBar;
use Amasty\GdprCookie\Model\CookieConsent;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;

class Allow extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    public function __construct(
        Context $context,
        CookieManager $cookieManager,
        Session $session,
        ConfigProvider $config,
        CookieConsentLogger $consentLogger
    ) {
        parent::__construct($context);

        $this->cookieManager = $cookieManager;
        $this->session = $session;
        $this->config = $config;
        $this->consentLogger = $consentLogger;
    }

    public function execute()
    {
        $customerId = $this->session->getCustomerId();

        if ($customerId) {
            $barType = $this->config->getCookiePrivacyBar();

            if ($barType !== CookiePolicyBar::NOTIFICATION) {
                $consentType = CookieConsent::CONFIRMATION_CONSENT;
            } else {
                $consentType = CookieConsent::NOTIFICATION_CONSENT;
            }
            $this->consentLogger->logCookieConsent($customerId, $consentType, 'All Allowed');
        }
        $this->cookieManager->setIsAllowCookies(CookieManager::ALLOWED_ALL);
    }
}
