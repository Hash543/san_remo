<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\WithoutConsent;

use Amasty\Gdpr\Controller\Adminhtml\AbstractWithoutConsent;
use Amasty\Gdpr\Model\ResourceModel\ConsentQueue\CollectionFactory as ConsentQueueCollectionFactory;
use Amasty\Gdpr\Model\ResourceModel\WithConsent\CollectionFactory as WithConsentCollectionFactory;
use Magento\Backend\App\Action;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;

class MassEmail extends AbstractWithoutConsent
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

    /**
     * @var CollectionFactory
     */
    private $customerCollectionFactory;

    public function __construct(
        Filter $filter,
        Action\Context $context,
        WithConsentCollectionFactory $withConsentCollectionFactory,
        ConsentQueueCollectionFactory $consentQueueCollectionFactory,
        CollectionFactory $customerCollectionFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->withConsentCollectionFactory = $withConsentCollectionFactory;
        $this->consentQueueCollectionFactory = $consentQueueCollectionFactory;
        $this->customerCollectionFactory = $customerCollectionFactory;
    }

    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider();
        /** @var \Magento\Customer\Model\ResourceModel\Customer\Collection $collection */
        $collection = $this->filter->getCollection($this->customerCollectionFactory->create());

        $customerIds = $collection->getColumnValues('entity_id');
        $this->consentQueueCollectionFactory->create()->insertIds($customerIds);

        $this->messageManager->addSuccessMessage(__('Customers were successfully added to email queue'));

        $this->_redirect('*/*');
    }
}
