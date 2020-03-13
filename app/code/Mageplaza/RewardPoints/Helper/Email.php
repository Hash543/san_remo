<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_RewardPoints
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPoints\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\Core\Helper\AbstractData;

/**
 * Class Email
 * @package Mageplaza\RewardPoints\Helper
 */
class Email extends AbstractData
{
    const CONFIG_MODULE_PATH                     = 'rewardpoints';
    const XML_PATH_EMAIL_SENDER                  = 'sender';
    const XML_PATH_UPDATE_TRANSACTION_EMAIL_TYPE = 'update';
    const XML_PATH_EXPIRE_EMAIL_TYPE             = 'expire';

    /**
     * @var Data
     */
    protected $helperData;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var Account
     */
    protected $accountHelper;

    /**
     * Email constructor.
     *
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     * @param Account $accountHelper
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
        Account $accountHelper
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->accountHelper = $accountHelper;

        parent::__construct($context, $objectManager, $storeManager);
    }

    /**
     * @param $customerId
     * @param $emailType
     * @param array $templateParams
     * @param string $sender
     * @param null $storeId
     * @param null $email
     *
     * @return $this
     */
    public function sendEmailTemplate(
        $customerId,
        $emailType,
        $templateParams = [],
        $sender = self::XML_PATH_EMAIL_SENDER,
        $storeId = null,
        $email = null
    ) {
        $customer = $this->accountHelper->getCustomerById($customerId);
        $storeId = $storeId ?: $customer->getStoreId();

        if (!$this->isEmailEnable('', $storeId) || !$this->isEmailEnable($emailType, $storeId)) {
            return $this;
        }

        $account = $this->accountHelper->getByCustomerId($customerId);
        if (!$account || !$account->getId() || !$account->getData('notification_' . $emailType)) {
            return $this;
        }

        $templateParams['customer_name'] = $customer->getName();

        try {
            $templateId = $this->getEmailConfig($emailType . '/template', $storeId);

            $transport = $this->transportBuilder->setTemplateIdentifier($templateId)
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => $storeId])
                ->setTemplateVars($templateParams)
                ->setFrom($this->getEmailConfig($sender, $storeId))
                ->addTo($email ?: $customer->getEmail(), $customer->getName())
                ->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_logger->critical($e->getMessage());
        }

        return $this;
    }

    /**
     * @param string $type
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEmailEnable($type = '', $storeId = null)
    {
        if (!$type) {
            return $this->getEmailConfig('enable', $storeId);
        }

        return $this->getEmailConfig($type . '/enabled', $storeId);
    }

    /**
     * @return bool
     */
    public function enableEmailNotification()
    {
        return $this->isEmailEnable() &&
               ($this->isEmailEnable(self::XML_PATH_UPDATE_TRANSACTION_EMAIL_TYPE) || $this->isEmailEnable(self::XML_PATH_EXPIRE_EMAIL_TYPE));
    }

    /**
     * @param $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getEmailConfig($code, $storeId = null)
    {
        return $this->getModuleConfig('email/' . $code, $storeId);
    }
}
