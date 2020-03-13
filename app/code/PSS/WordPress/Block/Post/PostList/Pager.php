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
namespace PSS\WordPress\Block\Post\PostList;

use Magento\Theme\Block\Html\Pager as MagentoPager;
use Magento\Framework\View\Element\Template\Context;
use PSS\WordPress\Model\OptionManager;
use Magento\Store\Model\ScopeInterface;

class Pager extends MagentoPager {
    /**
     * @var OptionManager
     */
	protected $optionManager;

    /**
     * Pager constructor.
     * @param Context $context
     * @param OptionManager $optionManager
     * @param array $data
     */
  public function __construct(
    Context $context,
    OptionManager $optionManager,
    array $data = []
  ) {
    $this->optionManager = $optionManager;
    parent::__construct($context, $data);
  }

	/**
	 * Construct the pager and set the limits
	 *
	 */
	protected function _construct()
	{
		parent::_construct();
		$this->setPageVarName('page');
		$baseLimit = $this->optionManager->getOption('posts_per_page', 10);
		$this->setDefaultLimit($baseLimit);
		$this->setLimit($baseLimit);
		$this->setAvailableLimit([$baseLimit => $baseLimit]);

		$this->setFrameLength(
			(int)$this->_scopeConfig->getValue(
				'design/pagination/pagination_frame',
				ScopeInterface::SCOPE_STORE
			)
		);
	}
	
	/**
	 * Return the URL for a certain page of the collection
	 * @param array $params
	 * @return string
	 */
	public function getPagerUrl($params = [])
	{
		$pageVarName = $this->getPageVarName();

		if (isset($params[$pageVarName])) {
			$slug = '/' . $pageVarName . '/' . $params[$pageVarName] . '/';
			unset($params[$pageVarName]);
		}
		else {
			$slug = '';
		}
		
		$pagerUrl = parent::getPagerUrl($params);
		
		if (strpos($pagerUrl, '?') !== false) {
			$pagerUrl = rtrim(substr($pagerUrl, 0, strpos($pagerUrl, '?')), '/') . $slug . substr($pagerUrl, strpos($pagerUrl, '?'));
		}
		else {
			$pagerUrl = rtrim($pagerUrl, '/') . $slug;
		}
		
		return $pagerUrl;
	}
}
