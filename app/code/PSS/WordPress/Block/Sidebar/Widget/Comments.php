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

class Comments extends AbstractWidget
{
    /**
     * Retrieve the recent comments collection
     * @return \PSS\WordPress\Model\ResourceModel\Post\Comment\Collection
     * @throws \Exception
     */
	public function getComments()
	{
		if (!$this->hasComments()) {
			$comments = $this->factory->create('Model\ResourceModel\Post\Comment\CollectionFactory')
				->create()
					->addCommentApprovedFilter()
					->addOrderByDate('desc');
			
			$comments->getSelect()->limit($this->getNumber() ? $this->getNumber() : 5 );
			
			$this->setComments($comments);
		}
		
		return $this->getData('comments');
	}
	
	/**
	 * Retrieve the default title
	 *
	 * @return string
	 */
	public function getDefaultTitle()
	{
		return __('Recent Comments');
	}
	
	/**
	 * Ensure template is set
	 *
	 * @return string
     * @throws \Exception
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('sidebar/widget/comments.phtml');
		}
		
		return parent::_beforeToHtml();
	}
}
