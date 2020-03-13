<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Observer\UserService;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use PSS\CRM\Model\UserRepository;




class Deletion implements ObserverInterface
{

    protected $userRepository;

    public function __construct(
        UserRepository $userRepository
    )
    {
        $this->userRepository = $userRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute(Observer $observer)
    {
        //TODO
        $customer = $observer->getEvent()->getCustomer();

        $result = $this->userRepository->delete($customer);

        /*
        if(isset($result[0])){
            switch($result[0]) {
                case '0':
                    break;
                default:
                    if($this->userHelper->getDebugEmail()!== null) {

                        foreach (explode(',',$this->userHelper->getDebugEmail()) as $email) {
                            $transport = $this->transportBuilder->setTemplateIdentifier('crm_error_template')
                                ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_ADMINHTML, 'store' => '0'])
                                ->setTemplateVars(
                                    [
                                        'method' => 'modification',
                                        'error_message' => 'There was a problem updating: ' . $customer->getEmail(),
                                        'trace_message' => json_encode($result)
                                    ]
                                )
                                ->setFrom('general')
                                ->addTo($email, 'CRM WS Error Receipt')
                                ->getTransport();
                            $transport->sendMessage();
                        }
                    }
                    throw new InvalidEmailOrPasswordException(__('There was a problem updating your information. Please contact Provider.'));
                    break;
            }

        }
        */


        //exit;

    }
}
