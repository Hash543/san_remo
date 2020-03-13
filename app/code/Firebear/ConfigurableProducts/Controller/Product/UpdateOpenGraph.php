<?php

namespace Firebear\ConfigurableProducts\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\View\LayoutFactory;


class UpdateOpenGraph extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;
    /**
     * @var PageFactory
     */
    private $resultPageFactory;
    /**
     * @var LayoutFactory
     */
    private $layoutFactory;

    /**
     * UpdateOpenGraph constructor.
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param PageFactory $resultPageFactory
     * @param LayoutFactory $layoutFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        LayoutFactory $layoutFactory
    ) {
        $this->layoutFactory     = $layoutFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * Ajax update Open Graph
     *
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $layout = $this->layoutFactory->create();
        $block  = $layout->createBlock('Firebear\ConfigurableProducts\Block\Product\View')
            ->setTemplate('Firebear_ConfigurableProducts::product/view/opengraph/general.phtml');
        $jsonResponse = [
            'openGraphHtml' => $block->toHtml()
        ];
        return $result->setData($jsonResponse);
    }
}