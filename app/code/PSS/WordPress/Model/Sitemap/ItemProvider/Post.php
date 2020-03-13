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
namespace PSS\WordPress\Model\Sitemap\ItemProvider;

class Post extends AbstractItemProvider
{
	/*
	 *
	 */
	protected function _getItems($storeId)
	{
		$storeBaseUrl =  rtrim($this->storeManager->getStore()->getBaseUrl(), '/');
		$collection   = $this->factory->create('PSS\WordPress\Model\ResourceModel\Post\Collection')->addIsViewableFilter();
		$items = [];
  
		foreach($collection as $post) {
			$relativePostUrl = ltrim(str_replace($storeBaseUrl, '', $post->getUrl()), '/');

			if (!$relativePostUrl) {
				// Probably post_type=page and set as homepage
				// Don't add as Magento will add this URL for us
				continue;
			}

			$postImages = [];
			
			if ($image = $post->getImage()) {
				$postImages = new \Magento\Framework\DataObject([
					'collection' => [new \Magento\Framework\DataObject(['url' => $image->getFullSizeImage()])],
					'title' => $post->getName(),
					'thumbnail' => $image->getAvailableImage(),
				]);
			}
			
			$items[] = $this->itemFactory->create([
				'url' => $relativePostUrl,
				'updatedAt' => $post->getPostModifiedDate('Y-m-d'),
				'images' => $postImages,
				'priority' => 0.5,
				'changeFrequency' => 'monthly',
			]);			
		}
		
		return $items;
	}
}