<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\WithConsent;

use Amasty\Gdpr\Controller\Adminhtml\AbstractWithConsent;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory as WithConsentCollectionFactory;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\CollectionFactory as ConsentQueueCollectionFactory;
use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;

class MassEmail extends AbstractWithConsent
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var WithConsentCollectionFactory
     */
    private $withConsentCollectionFactory;

    /**
     * @var ConsentQueueCollectionFactory
     */
    private $consentQueueCollectionFactory;

    public function __construct(
        Filter $filter,
        Action\Context $context,
        WithConsentCollectionFactory $withConsentCollectionFactory,
        ConsentQueueCollectionFactory $consentQueueCollectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->withConsentCollectionFactory = $withConsentCollectionFactory;
        $this->consentQueueCollectionFactory = $consentQueueCollectionFactory;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();
        /** @var \Amasty\Gdpr\Model\ResourceModel\WithConsent\Collection $collection */
        $collection = $this->filter->getCollection($this->withConsentCollectionFactory->create());

        $customerIds = $collection->getColumnValues('customer_id');
        $this->consentQueueCollectionFactory->create()->insertIds($customerIds);

        $this->messageManager->addSuccessMessage(__('Customers were successfully added to email queue'));

        $this->_redirect('*/*');
    }
}
