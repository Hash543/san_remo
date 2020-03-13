<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\GoogleWizard;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\LayoutFactory;
use Psr\Log\LoggerInterface;
use Amasty\Feed\Model\RegistryContainer;
use Amasty\Feed\Model\ResourceModel\GoogleWizard\Taxonomy\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;

class Search extends \Amasty\Feed\Controller\Adminhtml\GoogleWizard
{
    const LANGUAGE_CODE = 'language_code';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        RegistryContainer $registryContainer,
        LayoutFactory $resultLayoutFactory,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context, $registryContainer, $resultLayoutFactory, $logger);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $resultCategory = [];

        $params = $this->getRequest()->getParams();

        if ($params['source'] && $params['category']) {
            $categories = $this->collectionFactory->create()
                ->addFieldToFilter(RegistryContainer::TYPE_CATEGORY, ['like' => '%' . $params['category'] . '%'])
                ->addFieldToFilter(self::LANGUAGE_CODE, ['eq' => $params['source']])
                ->getData();

            foreach ($categories as $item) {
                $resultCategory[] = $item['category'];
            }
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($resultCategory);

        return $resultJson;
    }
}
