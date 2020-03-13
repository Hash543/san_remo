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

/* Parent Class */
use PSS\WordPress\Model\Meta\AbstractMeta;

/* Interface */
use PSS\WordPress\Api\Data\Entity\ViewableInterface;

class Archive extends AbstractModel implements ViewableInterface
{
	/*
	 *
	 */
	const ENTITY = 'wordpress_archive';

	/*
	 * @const string
	 */
	const CACHE_TAG = 'wordpress_archive';

	/*
	 *
	 */
	public function _construct()
	{
		$this->_init('\PSS\WordPress\Model\ResourceModel\Archive');
	}

	/*
	 *
	 */	
	public function getName()
	{
		return $this->wpContext->getDateHelper()->translateDate($this->_getData('name'));
	}
	
	/*
	 * Load an archive model by it's YYYY/MM
	 * EG: 2010/06
	 *
	 * @param string $value
	 */
	public function load($modelId, $field = NULL)
	{
		$this->setId($modelId);
		$extra = '';

		while(strlen($modelId . $extra) < 10) {
			$extra .= '/01';
		}

		if (strlen($modelId) === 7) {
			$format = 'F Y';
		}
		else if (strlen($modelId) === 4) {
			$format = 'Y';
		}
		else {
			$format = 'F j, Y';
			$this->setIsDaily(true);
		}

		$this->setName(date($format, strtotime($modelId . $extra . ' 01:01:01')));
		$this->setDateString(strtotime(str_replace('/', '-', $modelId . $extra) . ' 01:01:01'));

		return $this;
	}

	/*
	 * Get a date formatted string
	 *
	 * @param string $format
	 * @return string
	 */
	public function getDatePart($format)
	{
		return date($format, $this->getDateString());
	}

	/*
	 * Get the archive page URL
	 *
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url->getUrlWithFront($this->getId() . '/');
	}
	
	/*
	 * Determine whether posts exist for this archive
	 *
	 * @return bool
	 */
	public function hasPosts()
	{
		if ($this->hasData('post_count')) {
			return $this->getPostCount() > 0;
		}

		return count($this->getPostCollection()) > 0;
	}
	
	/*
	 * Retrieve a collection of blog posts
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Post\Collection
	 */
	public function getPostCollection()
	{
		if (!$this->hasPostCollection()) {
			$collection = parent::getPostCollection()
				->addIsViewableFilter()
				->addArchiveDateFilter($this->getId(), $this->getIsDaily())
				->setOrderByPostDate();

			$this->setPostCollection($collection);
		}
		
		return $this->getData('post_collection');
	}
	
	/*
	 *
	 *
	 * @return  string
	 */
	public function getContent()
	{
		return '';
	}

	/*
	 *
	 *
	 */
	public function getResourceCollection()
	{
		return false;
	}
}
