<?php

namespace Alfa9\StoreInfo\Block\Adminhtml\Stores\Edit\Buttons;

use Magento\Backend\Block\Widget\Context;
use Alfa9\StoreInfo\Api\StockistRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class Generic
{
    /**
     * @var Context
     */
    public $context;

    /**
     * @var StockistRepositoryInterface
     */
    public $stockistRepository;

    /**
     * @param Context $context
     * @param StockistRepositoryInterface $stockistRepository
     */
    public function __construct(
        Context $context,
        StockistRepositoryInterface $stockistRepository
    ) {
        $this->context = $context;
        $this->stockistRepository = $stockistRepository;
    }

    /**
     * Return Stockist page ID
     *
     * @return int|null
     */
    public function getStockistId()
    {
        try {
            return $this->stockistRepository->getById(
                $this->context->getRequest()->getParam('storeinfo_id')
            )->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
