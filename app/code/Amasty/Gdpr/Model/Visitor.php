<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Geoip\Model\Geolocation;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory;

class Visitor
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Geolocation
     */
    private $geolocation;

    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var CollectionFactory
     */
    private $withConsentCollectionFactory;

    public function __construct(
        Config $config,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        Geolocation $geolocation,
        RemoteAddress $remoteAddress,
        CollectionFactory $withConsentCollectionFactory
    ) {
        $this->config = $config;
        $this->customerSession = $customerSession;
        $this->checkoutSession = $checkoutSession;
        $this->geolocation = $geolocation;
        $this->remoteAddress = $remoteAddress;
        $this->withConsentCollectionFactory = $withConsentCollectionFactory;
    }

    public function isEEACustomer()
    {
        $customer = $this->customerSession->getCustomer();

        if ($countryCode = $this->checkoutSession->getQuote()->getShippingAddress()->getCountry()) {
            return $this->isEEACountry($countryCode);
        }

        if ($countryCode = $this->checkoutSession->getQuote()->getBillingAddress()->getCountry()) {
            return $this->isEEACountry($countryCode);
        }

        if ($customer && ($address = $customer->getPrimaryBillingAddress())) {
            if ($countryCode = $address->getCountry()) {
                return $this->isEEACountry($countryCode);
            }
        }

        if ($countryCode = $this->locate()) {
            return $this->isEEACountry($countryCode);
        } else {
            return false;
        }
    }

    protected function locate()
    {
        if ($this->customerSession->hasData('amgdpr_country')) {
            return $this->customerSession->getData('amgdpr_country');
        }

        $geolocationResult = $this->geolocation->locate($this->getRemoteIp());

        $result = isset($geolocationResult['country']) ? $geolocationResult['country'] : false;

        $this->customerSession->setData('amgdpr_country', $result);

        return $result;
    }

    public function getRemoteIp()
    {
        $ip = $this->remoteAddress->getRemoteAddress();
        $ip = substr($ip, 0, strrpos($ip, ".")) . '.0';

        return $ip;
    }

    protected function isEEACountry($countryCode)
    {
        return in_array($countryCode, $this->config->getEEACountryCodes());
    }

    /**
     * Checks if need to get new consent from customer
     *
     * @param \Amasty\Gdpr\Model\Policy|false $currentPolicy
     *
     * @return bool
     */
    public function isNeedConsent($currentPolicy)
    {
        if (!$this->customerSession->isSessionExists()) {
            $this->customerSession->start();
        }
        $customerId = $this->customerSession->getCustomerId();

        if ($customerId && $currentPolicy) {
            $withConsents = $this->withConsentCollectionFactory->create();
            $isConsentGiven = (bool)$withConsents
                ->addFieldToFilter('customer_id', $customerId)
                ->addFieldToFilter('policy_version', $currentPolicy->getPolicyVersion())
                ->getSize();

            if ($isConsentGiven) {
                return false;
            }
        }

        return true;
    }
}
