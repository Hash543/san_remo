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

class Form extends \PSS\WordPress\Block\AbstractBlock
{
	/**
	 * Ensure a valid template is set
	 *
	 * @return $this
	 */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate()) {
			$this->setTemplate('post/view/comment/form.phtml');
		}
		
		return parent::_beforeToHtml();		
	}

	/**
	 * Retrieve the comment form action
	 *
	 * @return string
	 */
	public function getCommentFormAction()
	{
		return $this->url->getSiteUrl('wp-comments-post.php');
	}

	/**
	 * Determine whether the customer needs to login before commenting
	 *
	 * @return bool
	 */
	public function customerMustLogin()
	{
		if ($this->optionManager->getOption('comment_registration')) {
			return !$this->wpContext->getCustomerSession()->isLoggedIn();
		}
		
		return false;
	}

	/**
	 * Retrieve the link used to log the user in
	 * If redirect to dashboard after login is disabled, the user will be redirected back to the blog post
	 *
	 * @return string
	 */
	public function getLoginLink()
	{
		$ref = $this->getPost() ? base64_encode($this->getPost()->getPermalink() . '#respond') : '';
		return $this->getUrl('customer/account/login', ['referer' => $ref]);
	}

	/**
	 * Returns true if the user is logged in
	 *
	 * @return bool
	 */
	public function isCustomerLoggedIn()
	{
		return $this->wpContext->getCustomerSession()->isLoggedIn();
	}
	
	/**
	 * Retrieve the current post object
	 *
	 * @return null|\PSS\WordPress\Model\Post
	 */
	public function getPost()
	{
		return $this->hasPost() ? $this->_getData('post') : $this->registry->registry('wordpress_post');
	}
	
	/**
	 * Returns the ID of the currently loaded post
	 *
	 * @return int|false
	 */
	public function getPostId()
	{
		return $this->getPost() ? $this->getPost()->getId() : false;
	}
}
