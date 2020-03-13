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
namespace PSS\WordPress\Block\Adminhtml\Category\Edit\Post;

class Grid extends \PSS\WordPress\Block\Adminhtml\Serializer\Post\Grid {
    /**
     * Ajax grid URL getter
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('adminhtml/wordpress/postcategory', ['_current' => true]);
    }
    /**
     * {@inheritdoc}
     */
    public function getPostByEntity()
    {
        /** @var \Magento\Catalog\Model\Category $product */
        $category = $this->coreRegistry->registry('current_category');
        $associationTypeId = $this->dataHelper->getTypeIdFromCategoryToPost();
        if ($category && $category->getId() && $associationTypeId) {
            $postIds = $this->dataHelper->getPostIdsRelated($associationTypeId, $category->getId());
            if (count($postIds) > 0) {
                return $postIds;
            }
        }
        return [];
    }
}