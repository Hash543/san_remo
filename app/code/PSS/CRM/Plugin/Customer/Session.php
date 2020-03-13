<?php
/**
 * AccountManagement
 *
 * @copyright Copyright © 2019 Alfa9 Servicios Web, S.L.. All rights reserved.
 * @author    xsanz@alfa9.com
 */

namespace PSS\CRM\Plugin\Customer;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use PSS\CRM\Model\UserRepository;

class Session
{

    /**
     * @var UserRepository
     */
    protected $userRepository;
    /**
     * @var \Mageplaza\RewardPoints\Helper\Data
     */
    protected $helperReward;

    protected $helperUser;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;
    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;


    /**
     * AccountManagement constructor.
     * @param \PSS\CRM\Api\UserRepositoryInterface $userRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Mageplaza\RewardPoints\Helper\Data $helperReward
     * @param \PSS\CRM\Helper\UserService $helperUser
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \PSS\CRM\Api\UserRepositoryInterface $userRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Mageplaza\RewardPoints\Helper\Data $helperReward,
        \PSS\CRM\Helper\UserService $helperUser,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->customerRepository = $customerRepository;
        $this->userRepository = $userRepository;
        $this->helperReward = $helperReward;
        $this->helperUser = $helperUser;
        $this->timezone = $timezone;
        $this->logger = $logger;
    }

    public function aroundSetCustomerDataAsLoggedIn(\Magento\Customer\Model\Session $subject, callable $proceed, $customerData)
    {

        $customer = $customerData;

        try {

            if($customerData->getCustomAttribute('id_crm')) { //IF CUSTOMER HAS CRM ID GET DATA
                $result = $this->userRepository->get($customerData);
                if ($result) {
                    $customer = $this->helperUser->updateCustomer($customerData, $result['resultadoDatosCliente']);
                    $this->updateLoyaltyPoints($customer, $result['resultadoDatosCliente']);
                } else { //NOT EXIST IN CRM, WE SENT TO CREATE
                    $result = $this->userRepository->create($customerData);
                    if ($result['resultado'] == 0) {
                        $customer = $this->helperUser->updateCustomer($customerData, $result['resultadoDatosCliente']);
                    }
                }
            } else { //IF CUSTOMER HAS NO CRM ID, WE SENT TO CREATE
                $result = $this->userRepository->create($customerData);
                if ($result['resultado'] == 0) {
                    $customer = $this->helperUser->updateCustomer($customerData, $result['resultadoDatosCliente']);
                }
            }

            $this->customerRepository->save($customer);

        }catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
        }

        $proceed($customer);


    }

    /**
     * @param CustomerInterface $customer
     * @param $soapArray
     * @throws \Exception
     */

    private function updateLoyaltyPoints(CustomerInterface $customer, $soapArray) {
        $loyaltyPoints = 0;
        if (isset($soapArray['a:NPuntosDefidelizacion'])) {
            $loyaltyPoints = (int)$soapArray['a:NPuntosDefidelizacion'];
        }
        if (isset($soapArray['a:fidelizacionesPuntosCliente'])
            && isset($soapArray['a:fidelizacionesPuntosCliente']['a:FidelizacionPuntosCliente'])) {
            $loyaltyDetails = [];
            foreach ($soapArray['a:fidelizacionesPuntosCliente']['a:FidelizacionPuntosCliente'] as $points) {
                if (!isset($points['a:FechaCaducidad']) || !isset($points['a:NPuntosGenerados'])) {
                    continue;
                }
                $loyaltyDetails[] = [
                    'points' => $points['a:NPuntosGenerados'],
                    'expiration_date' => $points['a:FechaCaducidad']
                ];
            }
            $account = $this->loadAccount($customer->getId());
            /** @var \Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Collection $customerPoints */
            $customerPoints = $this->helperReward->getTransaction()->getTransactionInFrontend($customer->getId());
            try {
                $this->cleanHistorical($customerPoints, $account);
            } catch (\Exception $exception) {
                $this->logger->notice(__("Error Deleting the History Reward Points."));
            }

            foreach ($loyaltyDetails as $detailPoint) {
                $expirationDate = $this->timezone->date(new \DateTime($detailPoint['expiration_date']))->format('Y-m-d H:i:s');
                try {
                    $transaction = $this->helperReward->getTransaction()
                        ->createTransaction(\Mageplaza\RewardPoints\Helper\Data::ACTION_ADMIN, $customer, new DataObject([
                            'is_active' => 1,
                            'point_amount' => $detailPoint['points'],
                            'expire_after' => 0,
                            'notification_update' => 0,
                            'notification_expire' => 0
                        ]));
                    $transaction->setData('expiration_date', $expirationDate)->save();
                } catch (\Exception $exception) {
                    $this->logger->critical($exception->getMessage());
                }
            }
            $account->setBalance($loyaltyPoints);
            try {
                $account->save();
            }catch (\Exception $exception) {
                $this->logger->critical($exception->getMessage());
            }

        }
    }
    /**
     * This should be more easy with an Id
     * @param \Mageplaza\RewardPoints\Model\ResourceModel\Transaction\Collection $collection
     * @param \Mageplaza\RewardPoints\Model\Account $account
     * @throws \Exception
     */
    private function cleanHistorical($collection, $account){
        /** @var \Mageplaza\RewardPoints\Model\Transaction $transactional */
        foreach ($collection as $transactional) {
            $transactional->delete();
        }
        $account->setBalance(0);
        $account->save();
    }

    /**
     * @param int $customerId
     * @return \Mageplaza\RewardPoints\Model\Account|mixed|null
     */
    private function loadAccount($customerId) {
        $accountData['is_active'] = 1;
        /** @var \Mageplaza\RewardPoints\Model\Account $account */
        try {
            $account = $this->helperReward->getAccountHelper()->create($customerId, $accountData);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $account = null;
        }
        return $account;
    }


}