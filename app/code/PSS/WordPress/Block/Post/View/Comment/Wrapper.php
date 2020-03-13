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
namespace PSS\WordPress\Block\Post\View\Comment;

class Wrapper extends AbstractComment {
    /**
     * Setup the pager and comments form blocks
     * @return AbstractComment|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('post/view/comment/wrapper.phtml');
		}

		if ($this->getCommentCount() > 0 && ($commentsBlock = $this->getChildBlock('comment_list')) !== false) {
			$commentsBlock->setComments($this->getComments());
		}	
			
		if ($this->getCommentCount() > 0 && ($pagerBlock = $this->getChildBlock('pager')) !== false) {
			$pagerBlock->setPost($this->getPost());
			$pagerBlock->setCollection($this->getComments());
		}

		if (($form = $this->getChildBlock('form')) !== false) {
			$form->setPost($this->getPost());
		}

		parent::_beforeToHtml();
	}

    /**
     * Get the comments HTML
     * @return string
     */
	public function getCommentsHtml()
	{
		return $this->getChildHtml('comment_list');
	}
}
