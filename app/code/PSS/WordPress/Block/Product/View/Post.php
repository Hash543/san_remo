<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 *
 * This package designed for Magento COMMUNITY edition
 * PSS Digital does not guarantee correct work of this extension
 * on any other Magento edition except Magento COMMUNITY edition.
 * PSS Digital does not provide extension support in case of * incorrect edition usage.
 *
 * @author PSS Digital Team
 * @category PSS
 * @package PSS_WordPress
 * @copyright Copyright (c) 2018 PSS (https://www.pss-ti.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
namespace PSS\WordPress\Block\Product\View;

class Post extends \PSS\WordPress\Block\PostEntity {

    /**
     * {@inheritdoc}
     */
    public function getAssociationTypeId() {
        return $this->dataHelper->getTypeIdFromProductToPost();
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityId(){
        if (!$this->hasData('product')) {
            $this->setData('product', $this->coreRegistry->registry('product'));
        }
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->getData('product');
        if($product) {
            return $product->getId();
        }
        return null;
    }
}