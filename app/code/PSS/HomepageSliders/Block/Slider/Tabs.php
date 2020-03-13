<?php

namespace PSS\HomepageSliders\Block\Slider;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Widget\Block\BlockInterface;
use WeltPixel\OwlCarouselSlider\Block\Slider\Category;

class Tabs extends Template implements BlockInterface {

    /**
     * @var Category
     */
    protected $_categorySliderBlock;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_categoryObject;

    /**
     * @var string
     */
    protected $_template = "slider/categories.phtml";

    /**
     * Tabs constructor.
     *
     * @param Context                         $context
     * @param Category                        $categorySliderBlock
     * @param \Magento\Catalog\Model\Category $categoryObject
     * @param array                           $data
     */
    public function __construct(
        Context $context,
        Category $categorySliderBlock,
        \Magento\Catalog\Model\Category $categoryObject,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_categorySliderBlock = $categorySliderBlock;
        $this->_categoryObject = $categoryObject;
    }

    /**
     * @return array
     */
    public function getSliderConfiguration() {
        $categoriesEnabled = [];
        $data = $this->getData();

        for($i = 1; $i <= 8; $i++) {
            unset($categorySlider);
            if($data['enable_category_' . $i]) {
                $categId = str_replace("category/", "", $data['category_' . $i]);

                $categorySlider = $this->_categorySliderBlock;
                $categorySlider->setData('category', $data['category_' . $i]);
                $category = $this->_categoryObject->load($categId);

                array_push($categoriesEnabled, [
                    'categName' => $category->getName(),
                    'categId' => $categId,
                    'categContent' => $categorySlider->toHtml(),
                ]);
            }
        }

        return $categoriesEnabled;
    }
}
