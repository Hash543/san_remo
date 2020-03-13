<?php



namespace Firebear\ConfigurableProducts\Api\Data;

interface ProductOptionsInterface
{
    /**#@+
     * Constants defined for keys of data array
     */
    const ENTITY_ID = 'item_id';
    const PRODUCT_ID = 'product_id';
    const COLOR_ATTR = 'color_code';
    const SIZE_ATTR = 'size_code';
    const LINKED_ATTRIBUTE_IDS = 'linked_attributes';

    /**
     * Returns entity_id field
     *
     * @return int|null
     */
    public function getItemId();

    /**
     * @param int $itemId
     *
     * @return $this
     */
    public function setItemId($itemId);
    
     /**
     * Returns product_id field
     *
     * @return int|null
     */
    public function getProductId();
    
     /**
     * @param int $productId
     *
     * @return $this
     */
    public function setProductId($productId);
    

    /**
     * Returns color_code field
     *
     * @return int|null
     */
    public function getColorCode();
    
     /**
     * @param int $colorCode
     *
     * @return $this
     */
    public function setColorCode($colorCode);
    
    /**
     * Returns size_code field
     *
     * @return int|null
     */
    public function getSizeCode();
    
     /**
     * @param int $sizeCode
     *
     * @return $this
     */
    public function setSizeCode($sizeCode);

    /**
     * Returns linked_attributes field
     *
     * @return string|null
     */

    public function getLinkedAttributes();

    /**
     * @param string $linkedAttributes
     *
     * @return $this
     */
    public function setLinkedAttributes($linkedAttributes);
}
