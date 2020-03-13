<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle;

class Single extends \Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option\Select
{
    protected $_template = 'product/view/type/bundle/options/single.phtml';
    protected $_template_swatch = 'product/view/type/bundle/options/single_swatch.phtml';
    protected $defaultTemplate = 'Magento_Bundle::catalog/product/view/type/bundle/option/select.phtml';

    private $configurableRenderer;
    private $anyRenderer;
    private $jsonDecoder;
    private $productRepository;
    private $productOptionRepository;
    private $imageFactory;
    private $firebearHelper;
    protected $quoteItemOption;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface         $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface         $jsonDecoder
     * @param \Magento\Catalog\Model\ProductRepository         $productRepository       ,
     * @param \Magento\Catalog\Model\Product\Option\Repository $productOptionRepository ,
     * @param \Magento\Catalog\Helper\Data                     $catalogData
     * @param \Magento\Framework\Registry                      $registry
     * @param \Magento\Framework\Stdlib\StringUtils            $string
     * @param \Magento\Framework\Math\Random                   $mathRandom
     * @param \Magento\Checkout\Helper\Cart                    $cartHelper
     * @param \Magento\Tax\Helper\Data                         $taxData
     * @param \Magento\Framework\Pricing\Helper\Data           $pricingHelper
     * @param array                                            $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Option\Repository $productOptionRepository,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Catalog\Helper\ImageFactory $imageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Checkout\Helper\Cart $cartHelper,
        \Magento\Tax\Helper\Data $taxData,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Firebear\ConfigurableProducts\Block\Product\Renderer\Configurable $configurableRenderer,
        \Firebear\ConfigurableProducts\Block\Product\Renderer\Any $anyRenderer,
        \Magento\Quote\Model\Quote\Item\OptionFactory $quoteItemOption,
        \Firebear\ConfigurableProducts\Helper\Data $firebearHelper,
        array $data = []
    ) {
        $this->pricingHelper  = $pricingHelper;
        $this->_catalogHelper = $catalogData;
        $this->_taxHelper     = $taxData;

        $this->anyRenderer             = $anyRenderer;
        $this->configurableRenderer    = $configurableRenderer;
        $this->jsonDecoder             = $jsonDecoder;
        $this->productRepository       = $productRepository;
        $this->productOptionRepository = $productOptionRepository;
        $this->quoteItemOption         = $quoteItemOption;
        $this->imageFactory            = $imageFactory;
        $this->firebearHelper          = $firebearHelper;
        if ($this->firebearHelper->getGeneralConfig('bundle_options/enable')) {
            if ($this->firebearHelper->getGeneralConfig('bundle_options/enable_swatch')) {
                $this->setTemplate($this->_template_swatch);
            } else {
                $this->setTemplate($this->_template);
            }
        } else {
            $this->setTemplate($this->defaultTemplate);
        }
        parent::__construct(
            $context,
            $jsonEncoder,
            $catalogData,
            $registry,
            $string,
            $mathRandom,
            $cartHelper,
            $taxData,
            $pricingHelper,
            $data
        );
    }

    public function getProductThumbnail($product, $width = 100, $height = 100)
    {
        return $this->imageFactory->create()->init(
            $product,
            'product_small_image',
            array(
                'width'  => $width,
                'height' => $height
            )
        )->getUrl();
    }

    public function mustShowImages()
    {
        if (count($this->getOption()->getSelections()) == 1 && $this->getOption()->getDefaultSelection() != null) {
            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getJsonConfig()
    {
        foreach ($this->getOption()->getSelections() as $selection) {
            if ($this->isSelected($selection) && $selection->getTypeId() == 'configurable') {
                $configurableProduct = $this->productRepository->getById($selection->getProductId());

                $configurableRenderer = clone($this->configurableRenderer);
                $configurableRenderer->setProduct($configurableProduct);

                return $configurableRenderer->getJsonConfig();
            }
        }

        return '{}';
    }

    /**
     * @return string
     */
    public function getJsonSwatchConfig()
    {
        foreach ($this->getOption()->getSelections() as $selection) {
            if ($this->isSelected($selection) && $selection->getTypeId() == 'configurable') {
                $configurableProduct = $this->productRepository->getById($selection->getProductId());

                $configurableRenderer = clone($this->configurableRenderer);
                $configurableRenderer->setProduct($configurableProduct);

                return $configurableRenderer->getJsonSwatchConfig();
            }
        }

        return '{}';
    }

    public function decorateArray($data)
    {
        return $this->configurableRenderer->decorateArray($data);
    }

    public function getConfigurableOptions($selection = null, $serialize = false)
    {
        $configurableOptionsBlock = $this->getOptionsBlock($selection);


        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productModel  = $objectManager->get('\Magento\Catalog\Model\Product')->load($selection->getProductId());

        $configurableOptionsBlock->setProduct($productModel);

        if ($serialize == false) {
            $optionHtml = '';

            foreach ($this->productOptionRepository->getProductOptions($productModel) as $_option) {
                $optionHtml .= $configurableOptionsBlock->getOptionHtml($_option);
            }

            return $optionHtml;
        } else {
            return $configurableOptionsBlock->getJsonConfig();
        }
    }

    protected function getOptionsBlock($selection = null)
    {
        if ($this->getLayout()->isBlock('configurableOptions' . $selection->getSelectionId())) {
            $this->getLayout()->unsetElement('configurableOptions' . $selection->getSelectionId());
        }

        $configurableOptionsBlock = $this->getLayout()->createBlock(
            'Magento\Catalog\Block\Product\View\Options',
            'configurableOptions' . $selection->getSelectionId()
        );
        $super_opt                = $this->getSuperOptions();

        $configurableOptionsBlock->addChild(
            'default',
            '\Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type\DefaultType',
            [
                'template'      => 'Firebear_ConfigurableProducts::product/view/type/bundle/options/type/default.phtml',
                'bundle_option' => $this->getOption()->getId()
            ]
        );

        $configurableOptionsBlock->addChild(
            'text',
            '\Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type\Text',
            [
                'template'      => 'Firebear_ConfigurableProducts::product/view/type/bundle/options/type/text.phtml',
                'bundle_option' => $this->getOption()->getId()
            ]
        );

        $configurableOptionsBlock->addChild(
            'file',
            '\Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type\File',
            [
                'template'      => 'Firebear_ConfigurableProducts::product/view/type/bundle/options/type/file.phtml',
                'bundle_option' => $this->getOption()->getId()
            ]
        );

        $configurableOptionsBlock->addChild(
            'select',
            '\Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type\Select',
            [
                'template'      => 'Firebear_ConfigurableProducts::product/view/type/bundle/options/type/select.phtml',
                'bundle_option' => $this->getOption()->getId()
            ]
        );

        $configurableOptionsBlock->addChild(
            'date',
            '\Firebear\ConfigurableProducts\Block\Product\View\Type\Bundle\Type\Date',
            [
                'template'      => 'Firebear_ConfigurableProducts::product/view/type/bundle/options/type/date.phtml',
                'bundle_option' => $this->getOption()->getId()
            ]
        );

        return $configurableOptionsBlock;
    }

    public function getMultiConfigurableOptionsAsJson()
    {
        $selections = [];

        foreach ($this->getOption()->getSelections() as $currentSelection) {
            $configurableOptionsBlock = $this->getOptionsBlock($currentSelection);
            $productModel             = $this->productRepository->getById($currentSelection->getProductId());
            $configurableOptionsBlock->setProduct($productModel);
            $configurableOptionsBlock->setOption($this->getOption());

            $options = $this->jsonDecoder->decode(
                $this->getConfigurableOptions($currentSelection, true)
            );

            foreach ($options as $optionId => $option) {
                $productOption         = $this->productOptionRepository->get($productModel->getSku(), $optionId);
                $selections[$optionId] = $configurableOptionsBlock->getOptionHtml($productOption);
            }
        }

        return $this->_jsonEncoder->encode($selections);
    }

    public function getConfigurableOptionsAsJson()
    {
        foreach ($this->getOption()->getSelections() as $currentSelection) {
            if ($this->isSelected($currentSelection)) {
                $block = $this->getLayout()->getBlock('configurableOptions' . $currentSelection->getSelectionId());

                return $block->getJsonConfig();
            }
        }

        return '{}';
    }

    public function getJsonConfigurableOptions()
    {
        return $this->_jsonEncoder->encode($this->getProduct()->getCustomOptions());

    }

    public function getSelectedProduct()
    {
        foreach ($this->getOption()->getSelections() as $currentSelection) {
            if ($this->isSelected($currentSelection)) {
                return $currentSelection->getSelectionId();
            }
        }

        return 0;
    }

    public function getBuyRequest()
    {
        return '{}';
    }

    public function getSelectionOptions()
    {
        foreach ($this->getOption()->getSelections() as $currentSelection) {
            if ($this->isSelected($currentSelection)) {
                $productModel = $this->productRepository->getById($currentSelection->getProductId());

                return $productModel->getOptions();
            }
        }

        return [];
    }

    public function getMultiConfigurableOptions()
    {
        $configurableOptions = [];

        foreach ($this->getOption()->getSelections() as $selection) {
            $configurableOptions[$selection->getSelectionId()] = $this->getConfigurableOptions($selection);
        }

        return $this->_jsonEncoder->encode($configurableOptions);
    }

    /**
     * @return string
     */
    public function getMultiJsonConfig()
    {
        $data = [];


        foreach ($this->getOption()->getSelections() as $selection) {
            $product = $this->productRepository->getById($selection->getProductId());

            if ($selection->getTypeId() == 'configurable') {
                $productRenderer = clone($this->configurableRenderer);
            } else {
                $productRenderer = clone($this->anyRenderer);
            }

            $productRenderer->setProduct($product);
            $data[$selection->getSelectionId()] = $this->jsonDecoder->decode($productRenderer->getJsonConfig());
        }

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * @return string
     */
    public function getMultiJsonSwatchConfig()
    {
        $data = [];

        foreach ($this->getOption()->getSelections() as $selection) {
            if ($selection->getTypeId() == 'configurable') {
                $configurableProduct  = $this->productRepository->getById($selection->getProductId());
                $configurableRenderer = clone($this->configurableRenderer);
                $configurableRenderer->setProduct($configurableProduct);
                $data[$selection->getSelectionId()] = $this->jsonDecoder->decode(
                    $configurableRenderer->getJsonSwatchConfig()
                );
            }

        }

        return $this->_jsonEncoder->encode($data);
    }

    /**
     * @return string
     */
    public function getMediaCallback()
    {
        foreach ($this->getOption()->getSelections() as $selection) {
            if ($selection->getTypeId() == 'configurable') {
                $configurableProduct = $this->productRepository->getById($selection->getProductId());
                $this->configurableRenderer->setProduct($configurableProduct);

                return $this->configurableRenderer->getMediaCallback();
            }
        }

        return '';
    }

    public function getSuperOptions()
    {
        if ($this->isEditProduct()) {

            $options = $this->quoteItemOption->create()->getCollection()->addFieldToFilter(
                'item_id',
                $this->getRequest()->getParam('id')
            )->addFieldToFilter('product_id', $this->getRequest()->getParam('product_id'))->addFieldToFilter(
                'code',
                'info_buyRequest'
            )->getFirstItem()->getData();
            $option  = unserialize($options['value']);

            return $option;
        } else {
            if ($this->getRequest()->getModuleName() == "sales") {
                $options = $this->quoteItemOption->create()->getCollection()->addFieldToFilter(
                    'item_id',
                    $this->getRequest()->getParam('id')
                )->addFieldToFilter('product_id', $this->getOption()->getParentId())->addFieldToFilter(
                    'code',
                    'info_buyRequest'
                )->getFirstItem()->getData();
                if (isset($options['value'])) {
                    $option = unserialize($options['value']);

                    return $option;
                }
            }
        }

        return array();
    }

    public function isEditProduct()
    {

        if ($this->getRequest()->getModuleName() == "checkout") {
            return true;
        } else {
            return false;
        }
    }
}
