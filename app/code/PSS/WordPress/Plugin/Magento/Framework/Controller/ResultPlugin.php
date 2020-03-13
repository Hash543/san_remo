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
namespace PSS\WordPress\Plugin\Magento\Framework\Controller;

use PSS\WordPress\Helper\AssetInjectorFactory;
use PSS\WordPress\Helper\AssetInjector;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\App\Response\Http as ResponseHttp;
use Magento\Framework\App\ResponseInterface;

class ResultPlugin
{
	/*
	 *
	 * @var \PSS\WordPress\Helper\AssetInjectorFactory
	 *
	 */
	protected $assetInjectorFactory;
	
	/*
	 * This is required for Magento 2.1.9 and lower as 2.1.9 doesn't pass
	 * method arguments to 'after' plugins. THis is fixed in 2.2.0
	 *
	 * @var \Magento\Framework\App\ResponseInterface
	 *
	 */
	protected $response;

	/*
	 *
	 * @param \PSS\WordPress\Helper\AssetInjectorFactory
	 *
	 */
	public function __construct(AssetInjectorFactory $assetInjectorFactory, ResponseHttp $response)
	{
		$this->response = $response;
		$this->assetInjectorFactory = $assetInjectorFactory;
	}
	
	/*
	 * Inject any required assets into the response body
	 *
	 * @param  \Magento\Framework\Controller\ResultInterface $subject
	 * @param  \Magento\Framework\Controller\ResultInterface $result
	 * @param  \Magento\Framework\App\Response\Http $respnse
	 * @return \Magento\Framework\Controller\ResultInterface
	 */
	public function afterRenderResult(ResultInterface $subject, ResultInterface $result, ResponseInterface $response = null)
	{
		// If Magento 2.1.9 or lower, $response won't be passed so load it separately
		if (!$response) {
			$response = $this->response;
		}
		
		if ($newBodyHtml = $this->assetInjectorFactory->create()->process($response->getBody())) {
			$response->setBody($newBodyHtml);
		}
		
		return $result;
	}
}
