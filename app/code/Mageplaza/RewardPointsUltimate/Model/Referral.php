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
 * @package     Mageplaza_RewardPointsUltimate
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\RewardPointsUltimate\Model;

use Mageplaza\RewardPointsPro\Model\Rules;

/**
 * Class Referral
 * @package Mageplaza\RewardPointsUltimate\Model
 */
class Referral extends Rules
{
    const CACHE_TAG          = 'mageplaza_rewardpoints_referral';
    const REFERRAL_GROUP_IDS = 'referral_group_ids';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mageplaza_rewardpoints_refer';

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();

        $this->_init('Mageplaza\RewardPointsUltimate\Model\ResourceModel\Referral');
    }

    /**
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $this->bindRuleToEntity($this->getResource(), 'referral_group_ids');
        parent::afterSave();
    }

    /**
     * {@inheritdoc}
     */
    protected function _afterLoad()
    {
        parent::_afterLoad();
        if (!$this->hasReferralGroupIds()) {
            $referralGroupIds = $this->_getResource()->getReferralGroupIds($this->getId());
            $this->setData(self::REFERRAL_GROUP_IDS, (array) $referralGroupIds);
        }
    }

    /**
     * @return mixed
     */
    public function getReferralRule()
    {
        $store = $this->storeManager->getStore();
        $rule = $this->getCollection()
            ->addFieldToFilter('is_active', 1)
            ->setValidationFilter(
                $this->_customerSession->getCustomerGroupId(),
                $store->getWebsiteId()
            )->getFirstItem();

        return $rule;
    }
}
