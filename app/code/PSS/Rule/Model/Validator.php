<?php
/**
 * @author Israel Yasis
 */

namespace PSS\Rule\Model;

class Validator extends \Magento\SalesRule\Model\Validator
{

    /**
     * @param string $listMarketingId
     * @return $this
     */
    public function setListMarketingId($listMarketingId)
    {
        $this->setData('list_marketing_id', $listMarketingId);
        return $this;
    }

    /**
     * @return string
     */
    public function getListMarketingId()
    {
        return $this->getData('list_marketing_id');
    }

    /**
     * Init validator
     * Init process load collection of rules for specific website,
     * customer group and coupon code
     *
     * @param int $websiteId
     * @param int $customerGroupId
     * @param string $couponCode
     * @param string $listMarketingId
     * @return $this
     */
    public function initValidator($websiteId, $customerGroupId, $couponCode, $listMarketingId)
    {

        $this->setWebsiteId($websiteId)->setCustomerGroupId($customerGroupId)->setCouponCode($couponCode);
        $this->setListMarketingId($listMarketingId);
        return $this;
    }

    /**
     * Get rules collection for current object state
     *
     * @param \Magento\Quote\Model\Quote\Address $address |null $address
     * @return \Magento\SalesRule\Model\ResourceModel\Rule\Collection
     */
    protected function _getRules(\Magento\Quote\Model\Quote\Address $address = null)
    {
        $addressId = $this->getAddressId($address);
        $key = $this->getWebsiteId() . '_'
            . $this->getCustomerGroupId() . '_'
            . $this->getCouponCode() . '_'
            . $this->getListMarketingId() . '_'
            . $addressId;
        if (!isset($this->_rules[$key])) {
            /** @var  \Magento\SalesRule\Model\ResourceModel\Rule\Collection $collection */
            $collection = $this->_collectionFactory->create()
                ->setValidationFilter(
                    $this->getWebsiteId(),
                    $this->getCustomerGroupId(),
                    $this->getCouponCode(),
                    null,
                    $address
                )->addFieldToFilter('is_active', 1);
            if (!empty($this->getListMarketingId())) {
                $collection = $collection->addFieldToFilter('marketing_list', ['like' => '%' . $this->getListMarketingId() . '%']);
            }
            $this->_rules[$key] = $collection->load();
        }
        return $this->_rules[$key];
    }
}