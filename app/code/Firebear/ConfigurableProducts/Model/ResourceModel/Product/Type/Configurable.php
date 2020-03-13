<?php

namespace Firebear\ConfigurableProducts\Model\ResourceModel\Product\Type;

use Magento\Catalog\Model\ResourceModel\Product\Relation as ProductRelation;
use Magento\Framework\Model\ResourceModel\Db\Context as DbContext;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;
use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\App\ObjectManager;

class Configurable extends \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
{
    /**
     * @var OptionProvider
     */
    private $optionProvider;

    /**
     * @param DbContext $context
     * @param ProductRelation $catalogProductRelation
     * @param string $connectionName
     * @param ScopeResolverInterface $scopeResolver
     * @param AttributeOptionProviderInterface $attributeOptionProvider
     * @param OptionProvider $optionProvider
     */
    public function __construct(
        DbContext $context,
        ProductRelation $catalogProductRelation,
        $connectionName = null,
        ScopeResolverInterface $scopeResolver = null,
        AttributeOptionProviderInterface $attributeOptionProvider = null,
        OptionProvider $optionProvider = null
    ) {
        parent::__construct(
            $context,
            $catalogProductRelation,
            $connectionName,
            $scopeResolver,
            $attributeOptionProvider,
            $optionProvider
        );
        $this->optionProvider = $optionProvider ?: ObjectManager::getInstance()->get(OptionProvider::class);
    }

    /**
     * Retrieve Required children ids
     *
     * @param array|int $parentId
     * @param bool $required
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChildrenIds($parentId, $required = true)
    {
        $select = $this->getConnection()->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('catalog_product_entity')],
            'p.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id',
            []
        )->where(
            'p.entity_id IN (?)',
            $parentId
        );
        $childrenIds = [
            0 => array_column(
                $this->getConnection()->fetchAll($select),
                'product_id',
                'product_id'
            )
        ];
        return $childrenIds;
    }
}
