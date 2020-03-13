<?php

namespace PSS\CRM\Model;

use PSS\CRM\Api\UserRepositoryInterface;
use PSS\CRM\Model\Api\UserService;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var UserService
     */
    protected $userService;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var \Magento\Framework\Api\ExtensionAttributesFactory
     */
    protected $extensionAttributesFactory;


    /**
     * UserRepository constructor.
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory
     * @param UserService $userService
     */
    public function __construct(
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionAttributesFactory,
        UserService $userService
    ) {
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->customerRepository = $customerRepository;
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
     */
    public function get(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        //GET CUSTOMER INFORMATION ON CRM
        return  $this->userService->query($customer);
    }

    // Ritz 22.08.2019
    public function create(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        //Update NEW CREATED DATA ON CRM
        //return $this->userService->creationSync($customer);
        return $this->userService->modifySync($customer);
    }

    public function delete(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        //DELETE CUSTOMER DATA ON CRM
        return $this->userService->deletionSync($customer);
    }

    public function modify(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        //MODIFY CUSTOMER DATA ON CRM
        return $this->userService->modifySync($customer);
    }
}
