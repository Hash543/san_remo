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
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;


class Query implements ObserverInterface
{

    protected $ticketRepository;
    protected $_productCollectionFactory;


    public function __construct(
        TicketRepository $ticketRepository,
        CollectionFactory $productCollectionFactory
    )
    {
        $this->ticketRepository = $ticketRepository;
        $this->_productCollectionFactory = $productCollectionFactory;

    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    public function execute(Observer $observer)
    {
        //TODO
        $quote = $observer->getEvent()->getQuote();
        $result = $this->ticketRepository->get($quote);

        //exit;
    }
}
