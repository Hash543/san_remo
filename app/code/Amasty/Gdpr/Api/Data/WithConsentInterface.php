<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Api\Data;

interface WithConsentInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const DATE_CONSENTED = 'date_consented';
    const POLICY_VERSION = 'policy_version';
    const GOT_FROM = 'got_from';
    const WEBSITE_ID = 'website_id';
    const IP = 'ip';
    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setId($id);

    /**
     * Get data
     *
     * @return mixed
     */
    public function getData();

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getDateConsented();

    /**
     * @param string $dateConsented
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setDateConsented($dateConsented);

    /**
     * @return string
     */
    public function getPolicyVersion();

    /**
     * @param string $policyVersion
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setPolicyVersion($policyVersion);

    /**
     * @param string $from
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setGotFrom($from);

    /**
     * @return string
     */
    public function getGotFrom();

    /**
     * @param int $websiteId
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setWebsiteId($websiteId);

    /**
     * @return int
     */
    public function getWebsiteId();

    /**
     * @param string $ip
     *
     * @return \Amasty\Gdpr\Api\Data\WithConsentInterface
     */
    public function setIp($ip);

    /**
     * @return string
     */
    public function getIp();
}
