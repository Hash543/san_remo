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
namespace PSS\WordPress\Controller\Post;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use PSS\WordPress\Model\Factory;
use PSS\WordPress\Model\OptionManager;
use Magento\Framework\App\CacheInterface;

class Invalidate extends Action
{
    /**
     * @var Factory
     */
	protected $factory;

    /**
     * @var OptionManager
     */
	protected $optionManager;

    /**
     * @var CacheInterface
     */
	protected $cacheManager;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
	protected $eventManager;

    /**
     * Invalidate constructor.
     * @param Context $context
     * @param OptionManager $optionManager
     * @param Factory $factory
     * @param CacheInterface $cacheManager
     */
	public function __construct(Context $context,OptionManager $optionManager, Factory $factory, CacheInterface $cacheManager)
	{
		$this->optionManager = $optionManager;
		$this->factory       = $factory;
		$this->cacheManager  = $cacheManager;
		$this->eventManager  = $context->getEventManager();

		parent::__construct($context);
	}

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
	public function execute()
	{
		$this->getResponse()->appendBody(
			json_encode([
				'result' => $this->invalidateCache() ? 'success' : 'failure'
			])
		);
	}

    /**
     * @return bool
     */
	protected function invalidateCache()
	{
		$postId = (int)$this->getRequest()->getParam('id');
		$nonce  = $this->getRequest()->getParam('nonce');
		
		if (!$this->verifyNonce($nonce, 'invalidate_' . $postId)) {
			return false;
		}

		$post = $this->factory->create('Post')->load($postId);
		
		if (!$post->getId()) {
			return false;
		}

		// Clean cache related objects and then allow FPC plugins to do the same
		$post->cleanModelCache();
		$this->eventManager->dispatch('clean_cache_by_tags', ['object' => $post]);

		return true;
	}

    /**
     * Validate given nonce
     * @param $nonce
     * @param $action
     * @return bool
     */
	protected function verifyNonce($nonce, $action)
	{
		if (!($salt = $this->optionManager->getOption('wordpress_salt'))) {
			return false;
		}

		$nonce_tick = ceil(time() / ( 86400 / 2 ));

		// 0-12 hours
		if (substr(hash_hmac('sha256', $nonce_tick . '|wordpress|' . $action, $salt), -12, 10) == $nonce) {
			return true;
		}

		// 12-24 hours
		if (substr(hash_hmac('sha256', ($nonce_tick - 1) . '|wordpress|' . $action, $salt), -12, 10) == $nonce) {
			return true;
		}

		return false;
	}
}
