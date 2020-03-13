<?php
/**
 * @author Israel Yasis
 */
namespace PSS\LayeredNavigation\Plugin\Controller\Xlanding\Page;

use WeltPixel\LayeredNavigation\Helper\Data as WpLayeredNavigationHelper;
/**
 * Class View
 * @package PSS\LayeredNavigation\Plugin\Controller\Xlanding\Page
 */
class View {

    /**
     * @var WpLayeredNavigationHelper
     */
    protected $wpHelper;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    /**
     * View constructor.
     * @param WpLayeredNavigationHelper $wpHelper
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     */
    public function __construct(
        WpLayeredNavigationHelper $wpHelper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
    ) {
        $this->wpHelper = $wpHelper;
        $this->jsonFactory = $jsonFactory;
    }
    /**
     * @param \Amasty\Xlanding\Controller\Page\View $subject
     * @param \Magento\Framework\View\Result\Page $result
     * @return \Magento\Framework\View\Result\Page|\Magento\Framework\Controller\Result\Json
     */
    public function afterExecute(
        \Amasty\Xlanding\Controller\Page\View $subject,
        \Magento\Framework\View\Result\Page $result
    ){
        if($subject->getRequest()->getParam('ajax') == 1 && $this->wpHelper->isEnabled()) {
            $layout = $result->getLayout();
            $resultsBlockHtml = $leftNavBlockHtml = '';
            if($blockProductList = $layout->getBlock('category.products.list')) {
                $resultsBlockHtml = $blockProductList->toHtml();
            }
            if($blockLeft = $layout->getBlock('catalog.leftnav')) {
                $leftNavBlockHtml = $blockLeft->toHtml();
            }
            $dataLayerContent = '';
            if ($dataLayerBlock = $layout->getBlock('head.additional')) {
                $dLBlockHtml = $dataLayerBlock->toHtml();

                preg_match('/var dlObjects = (.*?);/', $dLBlockHtml, $matches);
                if (count($matches) == 2) {
                    $dataLayerContent = $matches[1];
                }
            }
            return $this->jsonFactory->create()->setData(
                [
                    'success' => true,
                    'html' => [
                        'products_list' => $resultsBlockHtml,
                        'filters' => $leftNavBlockHtml,
                        'dataLayer' => $dataLayerContent
                    ]
                ]
            );
        } else {
            return $result;
        }
    }
}