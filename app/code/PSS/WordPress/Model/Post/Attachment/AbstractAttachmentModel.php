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
namespace PSS\WordPress\Model\Post\Attachment;

abstract class AbstractAttachmentModel extends \PSS\WordPress\Model\Post
{
    /**
     * {@inheritdoc}
     */
	public function _construct()
	{
		$this->setPostType('attachment');

		parent::_construct();
	}
	
	protected function _afterLoad()
	{
		$this->loadSerializedData();
		
		return parent::_afterLoad();
	}
	
	/**
	 * Load the serialized attachment data
	 *
	 */
	public function loadSerializedData()
	{
		if ($this->getId() > 0 && !$this->getIsFullyLoaded()) {
			$this->getResource()->loadSerializedData($this);
		}
	}
	
	public function getMetaValue($key)
	{
		return parent::getMetaValue('_wp_attachment_' . $key);
	}
}
