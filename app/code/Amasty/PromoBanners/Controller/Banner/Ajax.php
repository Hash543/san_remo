<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_PromoBanners
 */


namespace Amasty\PromoBanners\Controller\Banner;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;

class Ajax  extends Action
{
    /**
     * @var JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var RawFactory
     */
    protected $rawFactory;

    /**
     * @var DataFactory
     */
    protected $dataFactory;

    /**
     * @var \Amasty\PromoBanners\Model\Banner\Data
     */
    private $dataSource;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        RawFactory $rawFactory,
        \Amasty\PromoBanners\Model\Banner\Data $dataSource,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->rawFactory = $rawFactory;
        $this->dataSource = $dataSource;
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute()
    {
        if (!$this->getRequest()->isXmlHttpRequest()) {
            $resultRaw = $this->rawFactory->create();

            return $resultRaw->setHttpResponseCode(400);
        }

        // Required for proper render of product listings
        $this->resultPageFactory->create();

        $sections = $this->_request->getParam('sections', []);
        $context = $this->_request->getParam('context', []);
        $bannerIds = $this->_request->getParam('banners', []);

        // Init default values
        $context += [
            'currentProduct' => null,
            'currentCategory' => null,
            'searchQuery' => null,
        ];

        $productSku = $context['currentProduct'];
        $categoryId = ((int)$context['currentCategory']) ?: null;
        $searchQuery = $context['searchQuery'];

        $response = $this->dataSource->getBanners(
            $sections,
            $categoryId,
            $productSku,
            $searchQuery,
            $bannerIds
        );

        $resultJson = $this->jsonFactory->create();

        return $resultJson->setData($response);
    }
}
