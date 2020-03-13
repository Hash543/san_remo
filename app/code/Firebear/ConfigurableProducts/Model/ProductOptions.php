<?php


namespace Firebear\ConfigurableProducts\Model;

class ProductOptions extends \Magento\Framework\Model\AbstractModel implements \Firebear\ConfigurableProducts\Api\Data\ProductOptionsInterface
{
    /**
     * {@inheritdoc}
     */
    public function getItemId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ENTITY_ID, $itemId);
    }
    
    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($productId)
    {
        return $this->setData(self::PRODUCT_ID, $productId);
    }
    
     /**
     * {@inheritdoc}
     */
    public function getColorCode()
    {
       return $this->getData(self::COLOR_ATTR);
    }
    
     /**
     * {@inheritdoc}
     */
    public function setColorCode($colorCode)
    {
         return $this->setData(self::COLOR_ATTR, $colorCode);
    }
    
     /**
     * {@inheritdoc}
     */
    public function getSizeCode()
    {
        return $this->getData(self::SIZE_ATTR);
    }
    
     /**
     * {@inheritdoc}
     */
    public function setSizeCode($sizeCode)
    {
        return $this->setData(self::SIZE_ATTR, $sizeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getLinkedAttributes()
    {
        return $this->getData(self::LINKED_ATTRIBUTE_IDS);
    }

    /**
     * {@inheritdoc}
     */
    public function setLinkedAttributes($linkedAttributes)
    {
        return $this->setData(self::LINKED_ATTRIBUTE_IDS, $linkedAttributes);
    }

    /**
     * Initialize model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Firebear\ConfigurableProducts\Model\ResourceModel\ProductOptions');
    }
}
