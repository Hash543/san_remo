<?php
/**
 * Product description block
 *
 * @author Firebear Studio <fbeardev@gmail.com>
 */
namespace Firebear\ConfigurableProducts\Block\Product\View;

use Magento\Catalog\Model\AbstractModel;

class Attributes extends \Magento\Catalog\Block\Product\View\Attributes
{
    /**
     * Set product method
     *
     * @param AbstractModel $product
     *
     * @return $this
     */
    public function setProduct(AbstractModel $product)
    {
        $this->_product = $product;
        return $this;
    }
}
