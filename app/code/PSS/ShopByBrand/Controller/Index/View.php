<?php
/**
 * @author Israel Yasis
 * @description This Module helps to integrate the MagePlaza_ShopByBrand module with WeltPixel Layered Navigation module.
 */
namespace Pss\ShopByBrand\Controller\Index;
/**
 * Class View
 * @package Pss\ShopByBrand\Controller\Index
 */
class View extends \Mageplaza\Shopbybrand\Controller\Index\View {
    /**
     * @override
     */
    public function execute() {
        $isAjax = $this->getRequest()->getParam('ajax');

        if ($this->helper->isEnabled() && $isAjax) {

            $brand = $this->_initBrand();
            if(!$brand) {
                return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode(['success' => false]));
            }
            $this->getRequest()->setParam($this->helper->getAttributeCode(), $brand->getId());
            $page = $this->resultPageFactory->create();
            $page->getConfig()->addBodyClass('page-products');
            $layout = $page->getLayout();
            $dataLayerContent = '';
            $dataLayerBlock = $layout->getBlock('head.additional');
            if ($dataLayerBlock) {
                $dLBlockHtml = $dataLayerBlock->toHtml();

                preg_match('/var dlObjects = (.*?);/', $dLBlockHtml, $matches);
                if (count($matches) == 2) {
                    $dataLayerContent = $matches[1];
                }
            }
            $status = '';
            if ($this->helper->showQuickView()) {
                $imageUrl = $this->helper->getBrandImageUrl($brand);
                $brand->setImage($imageUrl);
                $status = 'ok';
            }
            $result = [
                'success'=> true,
                'html' => [
                    'products_list'   => $layout->getBlock('brand.category.products.list')->toHtml(),
                    'filters' => $layout->getBlock('catalog.leftnav')->toHtml(),
                    'brand'      => $brand->getData(),
                    'status'     => $status,
                    'dataLayer' => $dataLayerContent
                ]
            ];

            return $this->getResponse()->representJson($this->_jsonHelper->jsonEncode($result));
        } else {
            return parent::execute();
        }
    }
}