<?php
namespace PSS\CRM\Api;

interface UserRepositoryInterface
{

    /**
     * Returns information by user
     *
     * @api
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return array|boolean
     */
    public function get(\Magento\Customer\Api\Data\CustomerInterface $customer);
    public function create(\Magento\Customer\Api\Data\CustomerInterface $customer);
    public function delete(\Magento\Customer\Api\Data\CustomerInterface $customer);
    public function modify(\Magento\Customer\Api\Data\CustomerInterface $customer);


}
