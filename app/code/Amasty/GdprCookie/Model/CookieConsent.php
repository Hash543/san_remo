<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Model;

use Amasty\GdprCookie\Api\Data\CookieConsentInterface;
use Magento\Framework\Model\AbstractModel;

class CookieConsent extends AbstractModel implements CookieConsentInterface
{
    const NOTIFICATION_CONSENT = 0;

    const CONFIRMATION_CONSENT = 1;

    public function _construct()
    {
        $this->_init(ResourceModel\CookieConsent::class);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->_getData(CookieConsent::ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        $this->setData(CookieConsent::ID, $id);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(CookieConsent::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($id)
    {
        $this->setData(CookieConsent::CUSTOMER_ID, $id);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConsentType()
    {
        return $this->_getData(CookieConsentInterface::CONSENT_TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setConsentType($consentType)
    {
        $this->setData(CookieConsentInterface::CONSENT_TYPE, $consentType);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateRecieved()
    {
        return $this->_getData(CookieConsentInterface::DATE_RECIEVED);
    }

    /**
     * @inheritdoc
     */
    public function setDateRecieved($date)
    {
        $this->setData(CookieConsentInterface::DATE_RECIEVED, $date);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getConsentStatus()
    {
        return $this->_getData(CookieConsentInterface::CONSENT_STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setConsentStatus($status)
    {
        $this->setData(CookieConsentInterface::CONSENT_STATUS, $status);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWebsite()
    {
        return $this->_getData(CookieConsentInterface::WEBSITE);
    }

    /**
     * @inheritdoc
     */
    public function setWebsite($websiteId)
    {
        $this->setData(CookieConsentInterface::WEBSITE, $websiteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerIp()
    {
        return $this->_getData(CookieConsentInterface::CUSTOMER_IP);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerIp($ip)
    {
        $this->setData(CookieConsentInterface::CUSTOMER_IP, $ip);

        return $this;
    }
}
