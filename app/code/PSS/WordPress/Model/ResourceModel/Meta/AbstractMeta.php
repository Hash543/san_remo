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
namespace PSS\WordPress\Model\ResourceModel\Meta;

use PSS\WordPress\Model\ResourceModel\AbstractResource;

abstract class AbstractMeta extends AbstractResource
{
	/**
	 * Retrieve a meta value from the database
	 * This only works if the model is setup to work a meta table
	 * If not, null will be returned
	 *
	 * @param \PSS\WordPress\Model\Meta\AbstractMeta $object
	 * @param string $metaKey
	 * @param string $selectField
	 * @return null|mixed
	 */
	public function getMetaValue(\PSS\WordPress\Model\Meta\AbstractMeta $object, $metaKey, $selectField = 'meta_value')
	{
		$select = $this->getConnection()
			->select()
			->from($object->getMetaTable(), $selectField)
			->where($object->getMetaTableObjectField() . '=?', $object->getId())
			->where('meta_key=?', $metaKey)
			->limit(1);

		if (($value = $this->getConnection()->fetchOne($select)) !== false) {
			return trim($value);
		}
			
		return false;
	}

	/**
	 * Save a meta value to the database
	 * This only works if the model is setup to work a meta table
	 *
	 * @param \PSS\WordPress\Model\Meta\AbstractMeta $object
	 * @param string $metaKey
	 * @param string $metaValue
	 */
	public function setMetaValue(\PSS\WordPress\Model\Meta\AbstractMeta $object, $metaKey, $metaValue)
	{
		$metaValue = trim($metaValue);
		$metaData = array(
			$object->getMetaTableObjectField() => $object->getId(),
			'meta_key' => $metaKey,
			'meta_value' => $metaValue,
		);
						
		if (($metaId = $this->getMetaValue($object, $metaKey, $object->getMetaPrimaryKeyField())) !== false) {
			$this->getConnection()->update($object->getMetaTable(), $metaData, $object->getMetaPrimaryKeyField() . '=' . $metaId);
		}
		else {
			$this->getConnection()->insert($object->getMetaTable(), $metaData);
		}
	}
	
	/**
	 * Get an array of all of the meta values associated with this post
	 *
	 * @param \PSS\WordPress\Model\Meta\AbstractMeta $object
	 * @return false|array
	 */
	public function getAllMetaValues(\PSS\WordPress\Model\Meta\AbstractMeta $object)
	{
		$select = $this->getConnection()
			->select()
			->from($object->getMetaTable(), array('meta_key', 'meta_value'))
			->where($object->getMetaTableObjectField() . '=?', $object->getId());

		if (($values = $this->getConnection()->fetchPairs($select)) !== false) {
			return $values;
		}
		
		return false;
	}
}
