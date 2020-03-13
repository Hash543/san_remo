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

namespace PSS\WordPress\Block\Post\View;

class Comments extends \PSS\WordPress\Block\Post\View\Comment\AbstractComment
{
	/**
	 * Setup the pager and comments form blocks
	 *
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('post/view/comments.phtml');
		}

		if ($this->getCommentCount() > 0 && ($pagerBlock = $this->getChildBlock('pager')) !== false) {
			$pagerBlock->setCollection($this->getComments());
		}

		if (($form = $this->getChildBlock('form')) !== false) {
			$form->setPost($this->getPost());
		}

		parent::_beforeToHtml();
	}
	
	/**
	 * Get the HTML of the child comments
	 *
	 * @param \PSS\WordPress\Model\Post\Comment $comment
	 * @return string
	 */
	public function getChildrenCommentsHtml(\PSS\WordPress\Model\Post\Comment $comment)
	{
		return $this->getLayout()
			->createBlock(get_class($this))
			->setTemplate($this->getTemplate())
			->setParentId($comment->getId())
			->setComments($comment->getChildrenComments())
			->toHtml();
	}
}
