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
namespace PSS\WordPress\Model\Meta;

use PSS\WordPress\Model\AbstractModel;

abstract class AbstractMeta extends AbstractModel
{
	/**
	 * @var array
	 */
	protected $meta = [];
	
	/*
	 *
	 */
	abstract public function getMetaTableAlias();
	abstract public function getMetaTableObjectField();
	
	/**
	 * Determine whether a prefix is required
	 *
	 * @return bool
	 */
	public function doesMetaTableHavePrefix()
	{
		return false;
	}

	/**
	 * Retrieve the name of the meta database table
	 *
	 * @return false|string
	 */
	public function getMetaTable()
	{
		return $this->getResource()->getTable($this->getMetaTableAlias());
	}
	
	/**
	 * Retrieve the column name of the primary key fields
	 *
	 * @return string
	 */
	public function getMetaPrimaryKeyField()
	{
		return 'meta_id';
	}
	
	/**
	 * Retrieve a meta value
	 *
	 * @param string $key
	 * @return false|string
	 */
	public function getMetaValue($key)
	{
		if (!isset($this->meta[$key])) {
			$this->meta[$key] = $value = $this->getResource()->getMetaValue($this, $this->_getRealMetaKey($key));
		}
		
		return $this->meta[$key];
	}	
	
	/**
	 * Get an array of all of the meta values associated with this post
	 *
	 * @return false|array
	 */
	public function getAllMetaValues()
	{
		return $this->getResource()->getAllMetaValues($this);
	}
	
	/**
	 * Retrieve all of the meta data as an array
	 *
	 * @return false|array
	 */
	public function getMetaData()
	{
		return $this->meta;
	}
	
	/**
	 * Changes the wp_ to the correct table prefix
	 *
	 * @param string $key
	 * @return string
	 */
	protected function _getRealMetaKey($key)
	{
		if ($this->doesMetaTableHavePrefix() && $key) {
			if (($tablePrefix = $this->getResource()->getTablePrefix()) !== 'wp_') {
				if (preg_match('/^(wp_)(.*)$/', $key, $matches)) {
					return $tablePrefix . $matches[2];
				}
			}
		}
		return $key;	
	}
}
