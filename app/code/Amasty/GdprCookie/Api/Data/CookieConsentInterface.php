<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_GdprCookie
 */


namespace Amasty\GdprCookie\Api\Data;

interface CookieConsentInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ID = 'id';

    const CUSTOMER_ID = 'customer_id';

    const CONSENT_TYPE = 'consent_type';

    const DATE_RECIEVED = 'date_recieved';

    const CONSENT_STATUS = 'consent_status';

    const WEBSITE = 'website';

    const CUSTOMER_IP = 'customer_ip';

    /**#@-*/

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $id
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setCustomerId($id);

    /**
     * @return int
     */
    public function getConsentType();

    /**
     * @param int $consentType
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setConsentType($consentType);

    /**
     * @return string
     */
    public function getDateRecieved();

    /**
     * @param string $date
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setDateRecieved($date);

    /**
     * @return string
     */
    public function getConsentStatus();

    /**
     * @param string $status
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setConsentStatus($status);

    /**
     * @return int
     */
    public function getWebsite();

    /**
     * @param int $websiteId
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setWebsite($websiteId);

    /**
     * @return string
     */
    public function getCustomerIp();

    /**
     * @param string $ip
     *
     * @return \Amasty\GdprCookie\Api\Data\CookieConsentInterface
     */
    public function setCustomerIp($ip);
}
