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
use PSS\WordPress\Model\AbstractModel;

/* Interface */
use PSS\WordPress\Api\Data\Entity\ViewableInterface;

class Homepage extends AbstractModel implements ViewableInterface
{
	/*
	 * @var string
	 */
	const ENTITY = 'wordpress_homepage';

	/*
	 * @const string
	 */
	const CACHE_TAG = 'wordpress_homepage';
	
	/*
	 * @var
	 */    
  protected $staticPage;
    
	/*
	 *
	 *
	 * @return  string
	 */
	public function getName()
	{
		if ($staticPage = $this->getStaticFrontPage()) {
			return $staticPage->getName();
		}

		return $this->getBlogName();
	}

	/*
	 *
	 *
	 * @return  string
	 */
	public function getUrl()
	{
		if ($staticPage = $this->getStaticFrontPage()) {
			return $staticPage->getUrl();	
		}
		
		return $this->url->getUrl();
	}
		
	/*
	 *
	 *
	 * @return  string
	 */
	public function getContent()
	{
        if ($staticPage = $this->getFrontStaticPage()) {
          return $staticPage->getContent();
        }
		return $this->getBlogDescription();
	}
	
	/*
	 *
	 *
	 * @return 
	 */
	public function getFrontStaticPage()
	{
		if ($this->staticPage !== null) {
			return $this->staticPage;
		}
		
		$this->staticPage = false;

		if ((int)$this->getFrontStaticPageId() > 0) {
			$staticPage = $this->factory->create('Post')->load(
				$this->getStaticPageId()
			);
			
			if ($staticPage->getId()) {
				$this->staticPage = $staticPage;
			}
		}
		
		return $this->staticPage;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getFrontPageId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_on_front')) {
				return $pageId;
			}
		}
		
		return false;
	}
	
	/*
	 * If a page is set as a custom homepage, get it's ID
	 *
	 * @return false|int
	 */
	public function getPageForPostsId()
	{
		if ($this->optionManager->getOption('show_on_front') === 'page') {
			if ($pageId = $this->optionManager->getOption('page_for_posts')) {
				return $pageId;
			}
		}
		
		return false;
	}

	/*
	 *
	 *
	 */
	public function load($modelId, $field = null)
	{
		return $this;
	}

	/*
	 *
	 * @return string
	 */
	public function getRealHomepageUrl()
	{
		if (!$this->hasRealHomepageUrl()) {
			$this->setRealHomepageUrl($this->getUrl());

			if ($this->getFrontPageId()) {
				$page = $this->factory->create('PSS\WordPress\Model\Post')->setTaxonomy('page')->load($this->getFrontPageId());

				if ($page->getId()) {
					$this->setRealHomepageUrl($page->getUrl());
				}
			}
		}

		return $this->_getData('real_homepage_url');
	}
}
