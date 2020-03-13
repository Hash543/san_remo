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
namespace PSS\WordPress\Model\ResourceModel;

class AssociationType extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        $this->_init('wordpress_association_type', 'type_id');
    }

    /**
     * @param \PSS\WordPress\Model\AssociationType $associationType
     * @param $object
     * @param $wordpressObject
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadAssociationTypeByObjectName(\PSS\WordPress\Model\AssociationType $associationType, $object, $wordpressObject)
    {
        $connection = $this->getConnection();
        $bind = ['object' => $object, 'wordpress_object' => $wordpressObject];
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'object = :object'
        )->where(
            'wordpress_object = :wordpress_object'
        );
        $associationTypeId = $connection->fetchOne($select, $bind);
        if ($associationTypeId) {
            $this->load($associationType, $associationTypeId);
        } else {
            $associationType->setData([]);
        }
        return $this;

    }
}