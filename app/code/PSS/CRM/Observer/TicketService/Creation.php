<?php
/**
 *  @author Xavier Sanz <xsanz@pss.com>
 *  @copyright Copyright (c) 2017 PSS (http://www.pss.com)
 *  @package PSS
 */

namespace PSS\CRM\Observer\TicketService;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use PSS\CRM\Model\TicketRepository;





class Creation implements ObserverInterface
{

    protected $ticketRepository;


    public function __construct(
        TicketRepository $ticketRepository

    )
    {
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute(Observer $observer)
    {
        //TODO
        $order = $observer->getPayment()->getOrder();
        $result = $this->ticketRepository->create($order);

        exit;

    }
}
