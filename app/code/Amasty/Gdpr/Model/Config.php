<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const PATH_PREFIX = 'amasty_gdpr';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const PRIVACY_CHECKBOX_EEA_COUNTRIES = 'privacy_checkbox/eea_countries';

    const AVOID_ANONYMIZATION = 'general/avoid_anonymisation';

    const ORDER_STATUSES = 'general/order_statuses';

    const NOTIFICATE_ADMIN = 'deletion_notification/enable_admin_notification';

    const NOTIFICATE_ADMIN_TEMPLATE = 'deletion_notification/admin_template';

    const NOTIFICATE_ADMIN_SENDER = 'deletion_notification/admin_sender';

    const NOTIFICATE_ADMIN_RECIEVER = 'deletion_notification/admin_reciever';

    const EMAIL_NOTIFICATION_TEMPLATE = '_notification/template';

    const EMAIL_NOTIFICATION_SENDER = '_notification/sender';

    const EMAIL_NOTIFICATION_REPLY_TO = '_notification/reply_to';

    const ALLOWED = 'customer_access_control/';

    const REVOKE = 'revoke';

    const DOWNLOAD = 'download';

    const ANONYMIZE = 'anonymize';

    const DELETE = 'delete';

    const MODULE_ENABLED = 'general/enabled';

    /**#@-*/

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return array
     */
    public function getEEACountryCodes()
    {
        $codes = explode(',', $this->getValue(self::PRIVACY_CHECKBOX_EEA_COUNTRIES));

        return $codes;
    }

    /**
     * An alias for scope config with default scope type SCOPE_STORE
     *
     * @param string $key
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return string|null
     */
    public function getValue($key, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->getValue(self::PATH_PREFIX . '/' . $key, $scopeType, $scopeCode);
    }

    /**
     * @param string $path
     * @param string|null $scopeCode
     * @param string $scopeType
     *
     * @return bool
     */
    public function isSetFlag($path, $scopeCode = null, $scopeType = ScopeInterface::SCOPE_STORE)
    {
        return $this->scopeConfig->isSetFlag(self::PATH_PREFIX . '/' . $path, $scopeType, $scopeCode);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function isAdminDeleteNotificationEnabled($scopeCode = null)
    {
        return (bool)$this->getValue(self::NOTIFICATE_ADMIN, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAdminNotificationTemplate($scopeCode = null)
    {
        return $this->getValue(self::NOTIFICATE_ADMIN_TEMPLATE, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAdminNotificationSender($scopeCode = null)
    {
        return $this->getValue(self::NOTIFICATE_ADMIN_SENDER, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getAdminNotificationReciever($scopeCode = null)
    {
        return $this->getValue(self::NOTIFICATE_ADMIN_RECIEVER, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isAvoidAnonymization($scopeCode = null)
    {
        return $this->isSetFlag(self::AVOID_ANONYMIZATION, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getOrderStatuses($scopeCode = null)
    {
        return $this->getValue(self::ORDER_STATUSES, $scopeCode, ScopeInterface::SCOPE_WEBSITE);
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationEmailTemplate($configPath, $scopeCode = null)
    {
        return $this->getValue($configPath . self::EMAIL_NOTIFICATION_TEMPLATE, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationEmailSender($configPath, $scopeCode = null)
    {
        return $this->getValue($configPath . self::EMAIL_NOTIFICATION_SENDER, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return null|string
     */
    public function getConfirmationEmailReplyTo($configPath, $scopeCode = null)
    {
        return $this->getValue($configPath . self::EMAIL_NOTIFICATION_REPLY_TO, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param string      $configPath
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isAllowed($configPath, $scopeCode = null)
    {
        return $this->isSetFlag(self::ALLOWED . $configPath, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @param null|string $scopeCode
     *
     * @return bool
     */
    public function isModuleEnabled($scopeCode = null)
    {
        return $this->isSetFlag(self::MODULE_ENABLED, $scopeCode, ScopeInterface::SCOPE_STORE);
    }

}
