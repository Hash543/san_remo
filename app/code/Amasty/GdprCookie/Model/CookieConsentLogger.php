<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\CookieConsentRepositoryInterface;
use Amasty\Base\Model\GetCustomerIp;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class CookieConsentLogger
{
    /**
     * @var CookieConsentRepositoryInterface
     */
    private $cookieConsentRepository;

    /**
     * @var CookieConsentFactory
     */
    private $cookieConsentFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var GetCustomerIp
     */
    private $customerIp;

    public function __construct(
        CookieConsentRepositoryInterface $cookieConsentRepository,
        CookieConsentFactory $cookieConsentFactory,
        GetCustomerIp $customerIp,
        StoreManagerInterface $storeManager,
        DateTime $date
    ) {
        $this->cookieConsentRepository = $cookieConsentRepository;
        $this->cookieConsentFactory = $cookieConsentFactory;
        $this->storeManager = $storeManager;
        $this->date = $date;
        $this->customerIp = $customerIp;
    }

    public function logCookieConsent($customerId, $type, $status)
    {
        if (!$customerId) {
            return;
        }
        $cookieConsent = $this->cookieConsentRepository->getByCustomerId($customerId);
        $website = $this->storeManager->getWebsite()->getId();
        $customerIp = $this->getRemoteIp();

        if ($cookieConsent) {
            $cookieConsent->setCustomerId($customerId)
                ->setConsentType($type)
                ->setConsentStatus($status)
                ->setWebsite($website)
                ->setDateRecieved($this->date->gmtDate())
                ->setCustomerIp($customerIp);
            $this->cookieConsentRepository->save($cookieConsent);

            return;
        }
        $consent = $this->cookieConsentFactory->create();
        $consent->setCustomerId($customerId)
            ->setConsentType($type)
            ->setConsentStatus($status)
            ->setWebsite($this->storeManager->getWebsite()->getId())
            ->setCustomerIp($this->getRemoteIp());
        $this->cookieConsentRepository->save($consent);
    }

    public function getRemoteIp()
    {
        $ip = $this->customerIp->getCurrentIp();
        $ip = substr($ip, 0, strrpos($ip, ".")) . '.0';

        return $ip;
    }
}
