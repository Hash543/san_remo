<?php
namespace PSS\WordPress\Model\ResourceModel\Post\Attachment\Collection;
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

abstract class AbstractAttachmentCollection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{


    /**
	 * Load an attachment
	 *
	 * @param bool $printQuery
	 * @param bool $logQuery
	 * @return $this
	 */
    public function load($printQuery = false, $logQuery = false)
    {
		$this->getSelect()
			->where("post_type = ?", 'attachment')
			->where("post_mime_type LIKE 'image%'");
		return parent::load($printQuery, $logQuery);
    }
	
	/**
	 * Set the parent ID
	 *
	 * @param int $parentId = 0
	 * @return $this
	 */
	public function setParent($parentId = 0)
	{
		$this->getSelect()->where("post_parent = ?", $parentId);
		return $this;
	}
	
	/**
	 * After loading the collection, unserialize data
	 *
	 * @return $this
	 */
	protected function _afterLoad()
	{
		parent::_afterLoad();

		foreach($this->_items as $item) {
			$item->loadSerializedData();
		}
		
		return $this;
	}
}
