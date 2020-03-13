<?php
/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_SeoPro
 * @copyright   Copyright (c) 2016 Mageplaza (https://www.mageplaza.com/)
 * @license     http://mageplaza.com/LICENSE.txt
 */

namespace Firebear\ConfigurableProducts\Plugin\Seo;

use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\App\Request\Http;
use Magento\Framework\UrlInterface;

/**
 * Class SeoBeforeRender
 * @package Mageplaza\Seo\Plugin
 */
class Canonical
{

	/**
	 * @var \Magento\Framework\View\Page\Config
	 */
	protected $pageConfig;

	/**
	 * @var \Magento\Framework\App\Request\Http
	 */
	protected $request;

	/**
	 * @var \Magento\Framework\UrlInterface
	 */
	protected $url;

	/**
	 * SeoProRender constructor.
	 * @param \Magento\Framework\View\Page\Config $pageConfig
	 * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\UrlInterface     $url
     */
    function __construct(
        PageConfig $pageConfig,
        Http $request,
        UrlInterface $url
    ) {
        $this->pageConfig = $pageConfig;
        $this->request    = $request;
        $this->url        = $url;
    }

    /**
	 * @param \Magento\Framework\View\Page\Config\Renderer $subject
	 * @param $result
	 * @return mixed
	 */
	public function afterRenderMetadata(\Magento\Framework\View\Page\Config\Renderer $subject, $result)
	{	
		$this->pageConfig->addRemotePageAsset(
            $this->url->escape($this->url->getCurrentUrl()),
			'canonical',
			['attributes' => ['rel' => 'canonical']]
		);
		return $result;
	}
}
