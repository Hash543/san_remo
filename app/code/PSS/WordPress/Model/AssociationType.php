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
namespace PSS\WordPress\Model;

use PSS\WordPress\Model\ResourceModel\AssociationType as ResourceAssociationType;
/**
 * Class AssociationType
 * @method ResourceAssociationType _getResource()
 */
class AssociationType extends \Magento\Framework\Model\AbstractModel {

    const TYPE_ID = 'type_id';
    const OBJECT = 'object';
    const WORDPRESS_OBJECT = 'wordpress_object';

    const OBJECT_VALUE_PRODUCT = 'product';
    const OBJECT_VALUE_CATEGORY = 'category';
    const OBJECT_VALUE_CMS_PAGE = 'cms_page';
    const WORDPRESS_OBJECT_POST = 'post';
    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('PSS\WordPress\Model\ResourceModel\AssociationType');
    }

    /**
     * @param string $object
     * @param string $wordpressObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAssociationTypeByObjectName($object,  $wordpressObject){

        $this->_getResource()->loadAssociationTypeByObjectName($this, $object, $wordpressObject);
        return $this;
    }
}