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

namespace PSS\WordPress\Controller;

use Magento\Framework\App\Action\Action as ParentAction;
use Magento\Framework\App\Action\Context;
use PSS\WordPress\Model\Context as WPContext;

abstract class Action extends ParentAction
{
    /**
     * @var WPContext
     */
    protected $wpContenxt;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    /**
     * @var
     */
    protected $entity;
    /**
     * @var
     */
    protected $resultPage;

    /**
     * @var \PSS\WordPress\Model\Url
     */
    protected $url;

    /**
     * @var \PSS\WordPress\Model\Factory
     */
    protected $factory;

    /**
     * @return mixed
     */
    abstract protected function _getEntity();

    /**
     * Action constructor.
     * @param Context $context
     * @param WPContext $wpContext
     */
    public function __construct(
        Context $context,
        WPContext $wpContext
    ) {
        $this->wpContenxt = $wpContext;
        $this->registry = $wpContext->getRegistry();
        $this->url = $wpContext->getUrl();
        $this->factory = $wpContext->getFactory();
        parent::__construct($context);
    }

    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     * @return bool|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $this->_beforeExecute();

        if ($forward = $this->_getForwardForPreview()) {
            return $forward;
        }

        if ($forward = $this->_getForward()) {
            return $forward;
        }

        $this->checkForAmp();

        $this->_initLayout();

        $this->_afterExecute();

        return $this->getPage();
    }

    /**
     * @return bool
     */
    protected function _getForward()
    {
        return false;
    }

    /**
     * @return $this|boolean
     */
    protected function _beforeExecute()
    {
        if (($entity = $this->_getEntity()) === false) {
           return false;
        }

        if ($entity !== null) {
            $this->registry->register($entity::ENTITY, $entity);
        }

        return $this;
    }

    /*
       *
       */
    protected function _initLayout()
    {
        // Remove the default action layout handle
        // This allows controller to add handles in chosen order
        $this->getPage()->getLayout()->getUpdate()->removeHandle($this->getPage()->getDefaultLayoutHandle());

        if ($handles = $this->getLayoutHandles()) {
            foreach ($handles as $handle) {
                $this->getPage()->addHandle($handle);
            }
        }

        $this->getPage()->getConfig()->addBodyClass('is-blog');

        if ($breadcrumbsBlock = $this->_view->getLayout()->getBlock('breadcrumbs')) {
            if ($crumbs = $this->_getBreadcrumbs()) {
                foreach ($crumbs as $key => $crumb) {
                    $breadcrumbsBlock->addCrumb($key, $crumb);
                }
            }
        }

        return $this;
    }

    /**
     * Get an array of extra layout handles to apply
     * @return array
     */
    public function getLayoutHandles()
    {
        return ['wordpress_default'];
    }

    /**
     * Get the breadcrumbs
     * @return array
     */
    protected function _getBreadcrumbs()
    {
        $crumbs = [
            'home' => [
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link' => $this->url->getMagentoUrl()
            ]];

        if (!$this->url->isRoot()) {
            $crumbs['blog'] = [
                'label' => __('Blog'),
                'link' => $this->url->getHomeUrl()
            ];
        }

        return $crumbs;
    }

    /**
     * @return $this
     */
    protected function _afterExecute()
    {
        return $this;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function getPage()
    {
        if ($this->resultPage === null) {
            $this->resultPage = $this->resultFactory->create(
                \Magento\Framework\Controller\ResultFactory::TYPE_PAGE
            );
        }

        return $this->resultPage;
    }

    /**
     * @return mixed
     */
    public function getEntityObject()
    {
        if ($this->entity !== null) {
            return $this->entity;
        }

        return $this->entity = $this->_getEntity();
    }

    /*
       * @return bool
       */
    protected function _canPreview()
    {
        return false;
    }

    /*
     *
     */
    protected function _getForwardForPreview()
    {
        if (!$this->_canPreview()) {
            return false;
        }

        if ($this->getRequest()->getParam('preview') !== 'true') {
            return false;
        }

        $previewId = 0;

        if ($entity = $this->_getEntity()) {
            $previewId = (int)$entity->getId();
            $this->registry->unregister($entity::ENTITY);
        }

		foreach(['preview_id', 'p', 'page_id'] as $previewIdKey) {
			if (0 !== (int)$this->getRequest()->getParam($previewIdKey))	{
				$previewId = (int)$this->getRequest()->getParam($previewIdKey);

				break;
			}
		}

		if ($previewId) {
			return $this->resultFactory
				->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
				->setModule('wordpress')
				->setController('post')
				->setParams(['preview_id' => $previewId])
				->forward('preview');
		}

        return false;
    }

    /*
       *
       * @return bool
       *
       */
    public function checkForAmp()
    {
        return false;
    }

    /*
     *
     * @return \Magento\Framework\Controller\ResultForwardFactory
     *
     */
    protected function _getNoRouteForward()
    {
        return $this->resultFactory
            ->create(\Magento\Framework\Controller\ResultFactory::TYPE_FORWARD)
            ->setModule('cms')
            ->setController('noroute')
            ->forward('index');
    }
}