<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Shopbybrand
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

namespace Mageplaza\Shopbybrand\Block\Adminhtml\Form\Renderer;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Renderer\Fieldset\Element;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Mageplaza\Shopbybrand\Helper\Data as BrandHelper;
use Mageplaza\Shopbybrand\Model\BrandFactory;
use Mageplaza\Shopbybrand\Model\CategoryFactory;

/**
 * Class RenderDefaultAttributes
 * @package Mageplaza\LayeredNavigationUltimate\Block\Adminhtml\Form\Renderer
 */
class BrandCategory extends Element implements RendererInterface
{
    /** @var string Template */
    protected $_template = 'Mageplaza_Shopbybrand::category/brands.phtml';

    /**
     * @var \Mageplaza\Shopbybrand\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Mageplaza\Shopbybrand\Model\BrandFactory
     */
    protected $brandFactory;

    /**
     * @var \Mageplaza\Shopbybrand\Model\CategoryFactory
     */
    protected $brandCategoryFactory;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $systemStore;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * BrandCategory constructor.
     *
     * @param BrandHelper $helperData
     * @param BrandFactory $brandFactory
     * @param CategoryFactory $brandCategoryFactory
     * @param Store $systemStore
     * @param Registry $coreRegistry
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        BrandHelper $helperData,
        BrandFactory $brandFactory,
        CategoryFactory $brandCategoryFactory,
        Store $systemStore,
        Registry $coreRegistry,
        Context $context,
        array $data = []
    ) {
        $this->helperData = $helperData;
        $this->brandFactory = $brandFactory;
        $this->coreRegistry = $coreRegistry;
        $this->systemStore = $systemStore;
        $this->brandCategoryFactory = $brandCategoryFactory;

        parent::__construct($context, $data);
    }

    /**
     * render custom form element
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $this->_element = $element;
        $html = $this->toHtml();

        return $html;
    }

    /**
     * get brand collection
     * @return \Mageplaza\Shopbybrand\Block\Brand
     */
    public function getBrands()
    {
        return $this->brandFactory->create()->getBrandCollection();
    }

    /**
     * get all store as array
     * @return array
     */
    public function getStoreViews()
    {
        return $this->systemStore->getStoreValuesForForm();
    }

    /**
     * check is single store
     * @return bool
     */
    public function isSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }

    /**
     * @return array
     */
    public function getSelectedBrands()
    {
        $optionIds = [];
        $model = $this->coreRegistry->registry('current_brand_category');
        if ($model->getId()) {
            $optionIds = $this->brandCategoryFactory->create()->getCollection()
                ->addFieldToFilter('cat_id', $model->getId())
                ->getColumnValues('option_id');
        }

        return $optionIds;
    }
}
