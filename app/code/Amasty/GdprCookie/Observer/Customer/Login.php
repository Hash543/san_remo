<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Observer\Customer;

use Amasty\GdprCookie\Model\Config\Source\CookiePolicyBar;
use Amasty\GdprCookie\Model\CookieConsent;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Amasty\GdprCookie\Model\CookieManager;
use Amasty\GdprCookie\Model\CookieConsentLogger;
use Amasty\GdprCookie\Api\CookieConsentRepositoryInterface;
use Amasty\GdprCookie\Model\ConfigProvider;
use Amasty\GdprCookie\Model\Repository\CookieGroupsRepository;

class Login implements ObserverInterface
{
    /**
     * @var CookieManager
     */
    private $cookieManager;

    /**
     * @var CookieConsentLogger
     */
    private $consentLogger;

    /**
     * @var CookieConsentRepositoryInterface
     */
    private $consentRepository;

    /**
     * @var ConfigProvider
     */
    private $config;

    /**
     * @var CookieGroupsRepository
     */
    private $groupsRepository;

    public function __construct(
        CookieManager $cookieManager,
        CookieConsentLogger $consentLogger,
        CookieConsentRepositoryInterface $consentRepository,
        ConfigProvider $config,
        CookieGroupsRepository $groupsRepository
    ) {
        $this->cookieManager = $cookieManager;
        $this->consentLogger = $consentLogger;
        $this->consentRepository = $consentRepository;
        $this->config = $config;
        $this->groupsRepository = $groupsRepository;
    }

    /**
     * @param Observer $observer
     *
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        $cookies = $this->cookieManager->getAllowCookies();
        $barType = $this->config->getCookiePrivacyBar();

        if ($cookies === null) {
            return;
        }

        if ($cookies !== CookieManager::ALLOWED_ALL && $barType === CookiePolicyBar::CONFIRMATION) {
            $status = '';
            $groups = explode(',', $cookies);
            $cookieType = CookieConsent::CONFIRMATION_CONSENT;

            foreach ($groups as $group) {
                $groupName = $this->groupsRepository->getById($group)->getName();
                $status .= '<strong>' . $groupName . ':</strong> Allowed<br/>';
            }
        } elseif ($barType === CookiePolicyBar::CONFIRMATION) {
            $cookieType = CookieConsent::CONFIRMATION_CONSENT;
            $status = 'All Allowed';
        } elseif ($barType === CookiePolicyBar::NOTIFICATION) {
            $cookieType = CookieConsent::NOTIFICATION_CONSENT;
            $status = 'All Allowed';
        }
        $customerId = $observer->getData('customer')->getData('entity_id');

        $this->consentLogger->logCookieConsent($customerId, $cookieType, $status);
    }
}
