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

use PSS\WordPress\Block\AbstractBlock;

abstract class AbstractWidget extends AbstractBlock
{
    /**
     * Retrieve the default title
     * @return bool|mixed|string
     */
	public function getTitle()
	{
		if (($title = $this->_getData('title')) !== false) {
			return $title ? $title : $this->getDefaultTitle();
		}
		
		return false;
	}


    /**
     * @return string
     */
	public function getDefaultTitle()
	{
		return '';
	}
    /**
     * Attempt to load the widget information from the WordPress options table
     * @return AbstractBlock
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _beforeToHtml()
	{
		if ($this->getWidgetType()) {
			$data = $this->optionManager->getOption('widget_' . $this->getWidgetType());

			if ($data) {
				$data = unserialize($data);
				
				if (isset($data[$this->getWidgetIndex()])) {
					foreach($data[$this->getWidgetIndex()] as $field => $value) {
						$this->setData($field, $value);
					}
				}
			}
		}
		return parent::_beforeToHtml();
	}

    /**
     * Set some default values
     * @param array $defaults
     * @return $this
     */
	protected function _setDataDefaults(array $defaults)
	{
		foreach($defaults as $key => $value) {
			if (!$this->hasData($key)) {
				$this->setData($key, $value);
			}
		}
		
		return $this;
	}

    /**
     * Convert data values to something else
     * @param array $values
     * @return $this
     */
	protected function _convertDataValues(array $values)
	{
		foreach($this->getData() as $key => $value) {
			foreach($values as $find => $replace) {
				if ($value === $find) {
					$this->setData($key, $replace);
					continue;
				}
			}
		}
		return $this;
	}	

    /**
     * Retrieve the current page title
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _getPageTitle()
	{
		if (($headBlock = $this->getLayout()->getBlock('head')) !== false) {
			return $headBlock->getTitle();
		}
	
		return $this->_getWpOption('name');
	}

    /**
     * Retrieve the meta description for the page
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _getPageDescription()
	{
		if (($headBlock = $this->getLayout()->getBlock('head')) !== false) {
			return $headBlock->getDescription();
		}
		return '';
	}

    /**
     * Retrieve an ID to be used for the list
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getListId()
	{
		if (!$this->hasListId()) {
			$hash = 'wp-' . md5(rand(1111, 9999) . $this->getTitle() . $this->getWidgetType());
			
			$this->setListId(substr($hash, 0, 6));
		}
		
		return $this->_getData('list_id');
	}

	/**
	 *
	 *
	 * @return int
	 */
	public function getWidgetId()
	{
		return (int)$this->getWidgetIndex();
	}
}
