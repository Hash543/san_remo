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
namespace PSS\WordPress\Model\ResourceModel\Association;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection {

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('PSS\WordPress\Model\Association', 'PSS\WordPress\Model\ResourceModel\Association');
    }
    /**
     * @param int $typeId
     * @param int $objectId
     * @return $this
     */
    public function getRelationWordpressObjects( $typeId,  $objectId){
        $this->getSelect()->where('type_id = ?', $typeId)->where('object_id = ?', $objectId);
        return $this;
    }

    /**
     * @param int $typeId
     * @param int $magentoObjectId
     * @return $this
     */
    public function getRelationMagentoObjects( $typeId,  $magentoObjectId){
        $this->getSelect()->where('type_id = ?', $typeId)->where('wordpress_object_id = ?', $magentoObjectId);
        return $this;
    }
}