<?php
/**
 * @copyright: Copyright © 2017 Firebear Studio. All rights reserved.
 * @author   : Firebear Studio <fbeardev@gmail.com>
 */

namespace Firebear\ConfigurableProducts\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_CONFIG_COINPAYMENTS = 'firebear_configurableproducts/';

    private $containerData;
    private $catalogProduct;
    private $stockRegistry;
    protected $fields = ['color_code', 'size_code'];
    protected $_attributeFactory;
    protected $filterProvider;
    protected $storeManager;

    /**
     * Data constructor.
     *
     * @param Context $context
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider
    ) {
        $this->stockRegistry  = $stockRegistry;
        $this->catalogProduct = $catalogProduct;
        $this->_attributeFactory = $attributeFactory;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        parent::__construct($context);
        $this->defineContainerData();
    }
    /*
     * 
     */
    public function getAttrContent($attr) {
        $store_id = $this->storeManager->getStore()->getId();
        return $this->filterProvider->getBlockFilter()->setStoreId($store_id)->filter($attr);
    }
    
    public function defineContainerData()
    {
        $this->containerData = [
            'container_color_code' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement'   => 'container',
                            'componentType' => 'container',
                            'breakLine'     => '',
                            'label'         => 'Areas settings',
                            'required'      => 0,
                            'sortOrder'     => 0,
                            'component'     => 'Magento_Ui/js/form/components/group',
                            'dataScope'     => ''
                        ]
                    ]
                ],
                'children'  => [
                    'color_code' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType'      => 'text',
                                    'formElement'   => 'input',
                                    'visible'       => 1,
                                    'required'      => 0,
                                    'notice'        => 'Paste the attribute code for color, if null use default attribute "color"',
                                    'default'       => '',
                                    'label'         => 'Product attribute code for Color',
                                    'code'          => 'product_custom_color',
                                    'source'        => '',
                                    'globalScope'   => '',
                                    'sortOrder'     => 1,
                                    'componentType' => 'field',
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_size_code'  => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement'   => 'container',
                            'componentType' => 'container',
                            'breakLine'     => '',
                            'label'         => 'Areas settings',
                            'required'      => 0,
                            'sortOrder'     => 0,
                            'component'     => 'Magento_Ui/js/form/components/group',
                            'dataScope'     => ''
                        ]
                    ]
                ],
                'children'  => [
                    'size_code' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType'      => 'text',
                                    'formElement'   => 'input',
                                    'visible'       => 1,
                                    'required'      => 0,
                                    'notice'        => 'Paste the attribute code for size, if null use default attribute "volumen"',
                                    'default'       => 'volumen',
                                    'label'         => 'Product attribute code for Size',
                                    'code'          => 'product_custom_size',
                                    'source'        => '',
                                    'globalScope'   => '',
                                    'sortOrder'     => 2,
                                    'componentType' => 'field',
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'container_linked_attributes' => [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'formElement'   => 'container',
                            'componentType' => 'container',
                            'breakLine'     => '',
                            'label'         => 'Linked Attributes',
                            'required'      => 0,
                            'sortOrder'     => 0,
                            'component'     => 'Magento_Ui/js/form/components/group',
                            'dataScope'     => ''
                        ]
                    ]
                ],
                'children'  => [
                    'linked_attributes' => [
                        'arguments' => [
                            'data' => [
                                'config' => [
                                    'dataType'      => 'text',
                                    'formElement'   => 'multiselect',
                                    'visible'       => 1,
                                    'required'      => 0,
                                    'notice'        => '',
                                    'default'       => '',
                                    'label'         => 'Attributes list',
                                    'code'          => 'linked_attributes',
                                    'source'        => '',
                                    'globalScope'   => '',
                                    'sortOrder'     => 1,
                                    'componentType' => 'field',
                                    'options' => $this->getAttributesOptions(),
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @param      $field
     * @param null $storeId
     *
     * @return mixed
     */
    private function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param      $code
     * @param null $storeId
     *
     * @return mixed
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_CONFIG_COINPAYMENTS . $code, $storeId);
    }

    /**
     * @return array
     */
    public function getContainerData()
    {
        return $this->containerData;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param $product
     *
     * @return \Magento\Catalog\Model\Product[]
     */
    public function getAllowProducts($product)
    {

        $skipSaleableCheck = $this->catalogProduct->getSkipSaleableCheck();

        $products = $skipSaleableCheck
            ?
            $product->getTypeInstance()->getUsedProducts($product, null)
            :
            $product->getTypeInstance()->getSalableUsedProducts($product, null);

        return $products;
    }

    public function getChildInStock($product)
    {
        $inStock = [];
        foreach ($this->getAllowProducts($product) as $_product) {
            $stockItem = $this->stockRegistry->getStockItem($_product->getId(), 1);
            $saleable  = $stockItem->getIsInStock();
            if ($saleable) {
                $inStock[] = $_product;
            }
        }

        return $inStock;
    }

    /**
     * Get a list of product attributes
     *
     * @return array
     */
    public function getAttributesOptions()
    {
        $attributeInfo = $this->_attributeFactory->getCollection();
        $attributesOptions = [];
        foreach ($attributeInfo as $attributes) {
            if ($attributes->getEntityTypeId() == 4) {
                $attributesOptions[] = [
                    'value' => $attributes->getAttributeId(), 'label' => $attributes->getAttributeCode()];
            }
        }
        return $attributesOptions;
    }
}
