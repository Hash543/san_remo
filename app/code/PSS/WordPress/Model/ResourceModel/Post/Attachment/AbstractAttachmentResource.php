<?php
namespace PSS\WordPress\Model\ResourceModel\Post\Attachment;
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
 * @license http://opensource.org/licensevvvvs/osl-3.0.php Open Software License (OSL 3.0)
 */

use Magento\Framework\Model\ResourceModel\Db\Context;
use PSS\WordPress\Model\Context as WPContext;
use PSS\WordPress\Model\ResourceModel\Post as PostResource;

abstract class AbstractAttachmentResource extends PostResource
{


    public function loadSerializedData($attachment)
	{
		$attachment->setIsFullyLoaded(true);

		$select = $this->getConnection()
			->select()
			->from($this->getTable('wordpress_post_meta'), 'meta_value')
			->where('meta_key=?', '_wp_attachment_metadata')
			->where('post_id=?', $attachment->getId())
			->limit(1);

		$data = unserialize($this->getConnection()->fetchOne($select));

		if (is_array($data)) {
			foreach($data as $key => $value) {
				$attachment->setData($key, $value);
			}			
		}
		
		return $this;
	}
}
