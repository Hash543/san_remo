<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Observer\PromotionService;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use PSS\CRM\Model\PromotionRepository;




class CreationCoupon implements ObserverInterface
{

    protected $promotionRepository;
    protected $rulesRepository;
    protected $_coupon;


    public function __construct(
        PromotionRepository $promotionRepository,
        \Magento\SalesRule\Model\RuleRepository $rulesRepository,
        \Magento\SalesRule\Model\Coupon $coupon

    )
    {
        $this->promotionRepository = $promotionRepository;
        $this->rulesRepository = $rulesRepository;
        $this->_coupon = $coupon;

    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute(Observer $observer)
    {
        //TODO
        $rule = $observer->getEvent()->getRule();


        $result = $this->promotionRepository->createCoupon($rule);


    }
}
