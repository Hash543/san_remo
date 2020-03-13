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
namespace PSS\WordPress\Block\Sidebar\Widget;

class Cloud extends AbstractWidget
{
	/**
	 * Retrieve a collection of tags
	 *
	 * @return \PSS\WordPress\Model\ResourceModel\Term\Collection
     * @throws \Exception
	 */
	public function getTags()
	{
		if ($this->hasTags()) {
			return $this->_getData('tags');
		}
		
		$this->setTags(false);
		
		$tags = $this->factory->create('Model\ResourceModel\Term\Collection')
			->addCloudFilter($this->getTaxonomy())
			->setOrderByName()
			->load();

		if (count($tags) > 0) {
			$max = 0;
			$hasPosts = false;
			
			foreach($tags as $tag) {
				$max = $tag->getCount() > $max ? $tag->getCount() : $max;
				
				if ($tag->getCount() > 0) {
					$hasPosts = true;
				}
			}
			
			if ($hasPosts) {
				$this->setMaximumPopularity($max);
				$this->setTags($tags);
			}
		}

		return $this->getData('tags');
	}
	
	/**
	 * Retrieve a font size for a tag
	 *
	 * @param $tag
	 * @return int
     * @throws \Exception
	 */
	public function getFontSize($tag)
	{
		if ($this->getMaximumPopularity() > 0) {
			$percentage = ($tag->getCount() * 100) / $this->getMaximumPopularity();
			
			foreach($this->getFontSizes() as $percentageLimit => $default) {
				if ($percentage <= $percentageLimit) {
					return $default;
				}
			}
		}
		return 150;
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Tag Cloud');
	}
	
	/**
	 * Retrieve an array of font sizes
	 *
	 * @return array
     * @throws \Exception
	 */
	public function getFontSizes()
	{
		if (!$this->hasFontSizes()) {
			return array(
				25 => 90,
				50 => 100,
				75 => 120,
				90 => 140,
				100 => 150
			);
		}
		
		return $this->_getData('font_sizes');
	}

    /**
     * @return \PSS\WordPress\Block\AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/cloud.phtml');
		}
		
		return parent::_beforeToHtml();
	}
}
