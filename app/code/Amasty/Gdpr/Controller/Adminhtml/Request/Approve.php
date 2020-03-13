<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Api\DeleteRequestRepositoryInterface;
use Amasty\Gdpr\Model\Anonymizer;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class Approve extends RequestProcessAction
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $requestCollectionFactory;

    /**
     * @var DeleteRequestRepositoryInterface
     */
    private $requestRepository;

    /**
     * @var Anonymizer
     */
    private $anonymizer;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $requestCollectionFactory,
        DeleteRequestRepositoryInterface $requestRepository,
        Anonymizer $anonymizer
    ) {
        parent::__construct($context, $logger);
        $this->filter = $filter;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->requestRepository = $requestRepository;
        $this->anonymizer = $anonymizer;
    }

    /**
     * Mass action execution
     *
     * @throws LocalizedException
     */
    public function execute()
    {
        $this->filter->applySelectionOnTargetProvider(); // compatibility with Mass Actions on Magento 2.1.0
        /** @var Collection $collection */
        $collection = $this->filter->getCollection($this->requestCollectionFactory->create());

        if ($collection->count() > 0) {
            try {
                $action = function ($customerId) {
                    $this->anonymizer->deleteCustomer($customerId);
                };

                $customerIds = array_unique($collection->getColumnValues('customer_id'));
                $customerIdsToProcess = [];
                $rejected = 0;
                foreach ($customerIds as $customerId) {
                    $ordersData = $this->anonymizer->getCustomerActiveOrders($customerId);
                    if (!empty($ordersData)) {
                        $rejected++;
                    } else {
                        $customerIdsToProcess[] = $customerId;
                    }
                }

                $total = $this->processRequests($collection, $customerIdsToProcess, $action);

                if ($total) {
                    $this->messageManager->addSuccessMessage(
                        __('%1 customer(s) has been successfully deleted', $total)
                    );
                }

                if ($rejected) {
                    $this->messageManager->addErrorMessage(
                        __(
                            '%1 customer(s) has not been successfully deleted, because they have non-completed order(s)',
                            $rejected
                        )
                    );
                }
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
