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

use PSS\WordPress\Block\Post;
use PSS\WordPress\Model\Post\Comment;

abstract class AbstractComment extends Post
{
    /**
     * Returns a collection of comments for the current post
     * @return \PSS\WordPress\Model\ResourceModel\Post\Comment\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function getComments()
	{
		if ($this->hasComments()) {
			return $this->_getData('comments');
		}
		$this->setComments([]);
		if ($this->getCommentCount() > 0 && ($post = $this->getPost()) !== false) {
			$this->setComments($post->getResource()->getPostComments($post));

			if (($pager = $this->getChildBlock('pager')) !== false) {
				$this->_getData('comments')->setPageSize($pager->getLimit());
			}
		}
		return $this->_getData('comments');
	}

	/**
	 * Retrieve the amount of comments for the current post
	 *
	 * @return int
     * @throws \Exception
	 */
	public function getCommentCount()
	{
		return $this->getPost() ? $this->getPost()->getCommentCount() : 0;
	}
	
	/**
	 * Determine whether comments are enabled
	 *
	 * @return bool
     * @throws \Exception
	 */
	public function isCommentsEnabled()
	{
		return $this->getPost() && $this->getPost()->getCommentStatus() !== 'closed';
	}
		
	/**
	 * Get the comment content
	 * Filter out certain HTML tags
	 *
	 * @param \PSS\WordPress\Model\Post\Comment $comment
	 * @return string
     * @throws \Exception
	 */
	public function getCommentContent(Comment $comment)
	{
		$content = strip_tags(trim($comment->getCommentContent()), $this->getAllowedHtmlTags());
		
		return $this->canConvertNewLines() ? nl2br($content) : $content;
	}

    /**
     * @return string
     * @throws \Exception
     */
	public function getCommentsFormHtml()
	{
		return $this->getFormHtml();
	}
	
	/**
	 * Returns the HTML for the comment form
	 *
	 * @return string
     * @throws \Exception
	 */
	public function getFormHtml()
	{
		if ($this->isCommentsEnabled()) {
			return $this->getChildHtml('form');
		}
		
		return '';
	}
	
	/**
	 * Get the HTML for the pager block
	 *
	 * @return null|string
	 */
	public function getPagerHtml()
	{
		if ($this->optionManager->getOption('page_comments', false)) {
			return $this->getChildHtml('pager');
		}
	}
	
	/**
	 * Retrieve the allowed HTML tags as a string
	 *
	  * @return string
	  */
	public function getAllowedHtmlTags()
	{
		if (!$this->hasAllowedHtmlTags()) {
			return '<a><abbr><acronym><b><blockquote><cite><code><del><em><i><q><strike><strong>';
		}
		
		return $this->_getData('allowed_html_tags');
	}
	
	/**
	 * Determine whether to convert new lines to <br /> tags
	 * To disable this feature, call self::setConvertNewLines(false)
	 *
	 * @return bool
	 */
	public function canConvertNewLines()
	{
		return !$this->hasConvertNewLines() || (int)$this->getConvertNewLines() !== false;
	}
}
