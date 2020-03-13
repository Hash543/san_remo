<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Controller\Adminhtml\Request;

use Amasty\Gdpr\Model\DeleteRequest\Notifier;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\Collection;
use Amasty\Gdpr\Model\ResourceModel\DeleteRequest\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

class Send extends RequestProcessAction
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
     * @var Notifier
     */
    private $notifier;

    public function __construct(
        Action\Context $context,
        Filter $filter,
        LoggerInterface $logger,
        CollectionFactory $requestCollectionFactory,
        Notifier $notifier
    ) {
        parent::__construct($context, $logger);
        $this->filter = $filter;
        $this->requestCollectionFactory = $requestCollectionFactory;
        $this->notifier = $notifier;
    }

    public function execute()
    {
        $ids = $this->getRequest()->getParam('ids');
        $comment = $this->getRequest()->getParam('comment');

        if ($ids && $comment) {
            /** @var Collection $requestCollection */
            $requestCollection = $this->requestCollectionFactory->create();

            $requestCollection->addFieldToFilter('id', ['in' => explode(',', $ids)]);

            try {
                $action = function ($customerId) use ($comment) {
                    $this->notifier->notify($customerId, $comment);
                };

                $customerIds = array_unique($requestCollection->getColumnValues('customer_id'));

                $total = $this->processRequests($requestCollection, $customerIds, $action);

                $this->messageManager->addSuccessMessage(
                    __('%1 email(s) has been sent', $total)
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('An error has occurred'));
                $this->logger->critical($e);
            }
        }

        $this->_redirect('*/*');
    }
}
