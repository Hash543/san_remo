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
namespace PSS\WordPress\Model\ResourceModel\Image;

use PSS\WordPress\Model\ResourceModel\Post\Attachment\Collection\AbstractAttachmentCollection;

class Collection extends AbstractAttachmentCollection {

	public function _construct()
	{
		parent::_construct();
		
        $this->_init('PSS\WordPress\Model\Image', 'PSS\WordPress\Model\ResourceModel\Image');
	}
	
	/**
	 * Load an image
	 * Ensure that only images are returned
	 *
	 * @param bool $printQuery
	 * @param bool $logQuery
	 * @return $this
	 */
    public function load($printQuery = false, $logQuery = false)
    {
		$this->getSelect()->where("post_mime_type LIKE 'image%'");
		
		return parent::load($printQuery, $logQuery);
    }
}
