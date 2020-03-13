<?php

namespace Firebear\ConfigurableProducts\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\ProductRepository;


class LoadOptions extends Action
{
    private $resultJsonFactory;
    private $resultPageFactory;
    private $layoutFactory;
    private $productRepository;
    private $registry;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        PageFactory $resultPageFactory,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        ProductRepository $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->layoutFactory     = $layoutFactory;
        $this->productRepository = $productRepository;
        $this->registry          = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $layout = $this->layoutFactory->create();
        $this->registry->register('firebear_configurable_products_abstract_plugin',true);
        $block  = $layout->createBlock('Firebear\ConfigurableProducts\Block\Product\View\Options')
            ->setTemplate('Magento_Catalog::product/view/options.phtml');

        $block_links1 = $layout->createBlock(
            'Firebear\ConfigurableProducts\Block\Product\View\Type\DefaultType',
            'default'
        )
            ->setTemplate('Magento_Catalog::product/view/options/type/default.phtml');
        $block->setChild('default', $block_links1);

        $block_links2 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Text', 'text')
            ->setTemplate('Magento_Catalog::product/view/options/type/text.phtml');
        $block->setChild('text', $block_links2);

        $block_links3 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\File', 'file')
            ->setTemplate('Magento_Catalog::product/view/options/type/file.phtml');
        $block->setChild('file', $block_links3);

        $block_links4 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Select', 'select')
            ->setTemplate('Magento_Catalog::product/view/options/type/select.phtml');
        $block->setChild('select', $block_links4);

        $block_links5 = $layout->createBlock('Magento\Catalog\Block\Product\View\Options\Type\Date', 'date')
            ->setTemplate('Magento_Catalog::product/view/options/type/date.phtml');
        $block->setChild('date', $block_links5);

        $jsonResponse = [
            'optionsHtml' => $block->toHtml()
        ];
        $this->registry->unregister('firebear_configurable_products_abstract_plugin');

        return $result->setData($jsonResponse);
    }
}