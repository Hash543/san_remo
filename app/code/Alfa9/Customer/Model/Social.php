<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SocialLogin
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Alfa9\Customer\Model;

use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\State\InputMismatchException;

/**
 * Class Social
 *
 * @package Mageplaza\SocialLogin\Model
 */
class Social extends \Mageplaza\SocialLogin\Model\Social {
    /**
     * @param $data
     * @param $store
     *
     * @return mixed
     * @throws Exception
     */
    public function createCustomerSocial($data, $store) {
        /** @var CustomerInterface $customer */
        $customer = $this->customerDataFactory->create();
        $customer->setFirstname($data['firstname'])
                 ->setLastname($data['lastname'])
                 ->setEmail($data['email'])
                 ->setDob('1970-01-01')
                 ->setGender(1)
                 ->setTaxvat('00000000T')
                 ->setStoreId($store->getId())
                 ->setWebsiteId($store->getWebsiteId())
                 ->setCreatedIn($store->getName());

        try {
            // If customer exists existing hash will be used by Repository
            $customer = $this->customerRepository->save($customer);

            $objectManager = ObjectManager::getInstance();
            $mathRandom = $objectManager->get('Magento\Framework\Math\Random');
            $newPasswordToken = $mathRandom->getUniqueHash();
            $accountManagement = $objectManager->get('Magento\Customer\Api\AccountManagementInterface');
            $accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);

            if($this->apiHelper->canSendPassword($store)) {
                $this->getEmailNotification()->newAccount($customer, EmailNotificationInterface::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD);
            }

            $this->setAuthorCustomer($data['identifier'], $customer->getId(), $data['type']);
        } catch(AlreadyExistsException $e) {
            throw new InputMismatchException(
                __('A customer with the same email already exists in an associated website.')
            );
        } catch(Exception $e) {
            if($customer->getId()) {
                $this->_registry->register('isSecureArea', true, true);
                $this->customerRepository->deleteById($customer->getId());
            }
            throw $e;
        }

        /** @var Customer $customer */
        $customer = $this->customerFactory->create()->load($customer->getId());

        return $customer;
    }
}
