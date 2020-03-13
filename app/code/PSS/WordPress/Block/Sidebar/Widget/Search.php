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

class Search extends AbstractWidget
{
	/**
	 * Retrieve the action URL for the search form
	 *
	 * @return string
	 */
	public function getFormActionUrl()
	{
		return $this->url->getUrl('search') . '/';
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Search');
	}
	
	/**
	 * Retrieve the search term used
	 *
	 * @return string
	 */
	public function getSearchTerm()
	{
		return '';
	}
	
	/**
	 * Ensure template is set
	 *
	 * @return string
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('PSS_WordPress::sidebar/widget/search.phtml');
		}
		
		return parent::_beforeToHtml();
	}
}
