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
namespace PSS\WordPress\Block\Post;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;
use PSS\WordPress\Model\Context as WPContext;

/**
 * Class View
 * @package PSS\WordPress\Block\Post
 */
class View extends \PSS\WordPress\Block\Post {
    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $filterProvider;

    /**
     * View constructor.
     * @param Context $context
     * @param WPContext $wpContext
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        WPContext $wpContext,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        array $data = [] ){
        $this->filterProvider = $filterProvider;
        parent::__construct($context, $wpContext, $data);
    }

    /**
     * @return \PSS\WordPress\Block\Post
     */
	protected function _prepareLayout()
	{
		$this->getPost()->applyPageConfigData($this->pageConfig);
        
		return parent::_prepareLayout();
	}

    /**
     * @return \PSS\WordPress\Block\Post
     */
	protected function _beforeToHtml()
	{
		if (!$this->getTemplate() && $this->getPost()) {
			$postType = $this->getPost()->getTypeInstance();
			$this->setTemplate('PSS_WordPress::post/view.phtml');

			if ($postType->getPostType() !== 'post') {
				$postTypeTemplate = 'PSS_WordPress::' . $postType->getPostType() . '/view.phtml';

				if ($this->getTemplateFile($postTypeTemplate)) {
					$this->setTemplate($postTypeTemplate);
				}
			}
		}
		return parent::_beforeToHtml();
	}

    /**
     * @param string $html
     * @return string
     */
	public function filterContentCms($html) {
	    try{
            $store = $this->_storeManager->getStore();
            return $this->filterProvider->getBlockFilter()->setStoreId($store->getId())->filter($html);
        }catch (NoSuchEntityException $exception) {
	        return '';
        }
    }

}
