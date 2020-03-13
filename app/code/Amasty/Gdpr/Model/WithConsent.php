<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Amasty\Gdpr\Api\Data\WithConsentInterface;
use Amasty\Gdpr\Model\ResourceModel\WithConsent as WithConsentResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class WithConsent extends AbstractModel implements WithConsentInterface, IdentityInterface
{
    const CACHE_TAG = 'amasty_checkbox';

    /**
     * @var string
     */
    protected $_cacheTag = true;

    public function _construct()
    {
        parent::_construct();

        $this->_init(WithConsentResource::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(WithConsentInterface::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($customerId)
    {
        $this->setData(WithConsentInterface::CUSTOMER_ID, $customerId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getDateConsented()
    {
        return $this->_getData(WithConsentInterface::DATE_CONSENTED);
    }

    /**
     * @inheritdoc
     */
    public function setDateConsented($dateConsented)
    {
        $this->setData(WithConsentInterface::DATE_CONSENTED, $dateConsented);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPolicyVersion()
    {
        return $this->_getData(WithConsentInterface::POLICY_VERSION);
    }

    /**
     * @inheritdoc
     */
    public function setPolicyVersion($policyVersion)
    {
        $this->setData(WithConsentInterface::POLICY_VERSION, $policyVersion);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getGotFrom()
    {
        return $this->_getData(WithConsentInterface::GOT_FROM);
    }

    /**
     * @inheritdoc
     */
    public function setGotFrom($from)
    {
        $this->setData(WithConsentInterface::GOT_FROM, $from);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getWebsiteId()
    {
        return $this->_getData(WithConsentInterface::WEBSITE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setWebsiteId($websiteId)
    {
        $this->setData(WithConsentInterface::WEBSITE_ID, $websiteId);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIp()
    {
        return $this->_getData(WithConsentInterface::IP);
    }

    /**
     * @inheritdoc
     */
    public function setIp($ip)
    {
        $this->setData(WithConsentInterface::IP, $ip);

        return $this;
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG];
    }

    /**
     * Get list of cache tags applied to model object.
     *
     * @return array
     */
    public function getCacheTags()
    {
        $tags = parent::getCacheTags();
        if (!$tags) {
            $tags = [];
        }
        return $tags + $this->getIdentities();
    }
}
