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
namespace PSS\WordPress\Model\Integration;

/* Constructor Args */
use PSS\WordPress\Model\Theme;
use PSS\WordPress\Model\Url;

/* Misc */
use PSS\WordPress\Model\Integration\IntegrationException;

class UrlTest
{
	/*
	 *
	 *
	 */
	protected $theme;
	
	/*
	 *
	 *
	 */
	protected $url;

	/*
	 *
	 *
	 */
	public function __construct(Theme $theme, Url $url)
	{
		$this->theme = $theme;
		$this->url   = $url;
	}

    /**
     * @return $this
     */
	public function runTest()
	{
		if (!$this->theme->isThemeIntegrated()) {
			return $this;
		}

		$magentoUrl = $this->url->getMagentoUrl();
		$homeUrl    = $this->url->getHomeUrl();
		$siteUrl    = $this->url->getSiteUrl();
		
		if ($homeUrl === $siteUrl) {
			IntegrationException::throwException(
				sprintf('Your WordPress Home URL matches your Site URL (%s). Your SiteURL should be the WordPress installation URL and the WordPress Home URL should be the integrated blog URL.', $siteUrl)
			);
		}

		if ($this->url->isRoot()) {
			if ($homeUrl !== $magentoUrl) {
				IntegrationException::throwException(
					sprintf('Your home URL (%s) is incorrect and should match your Magento URL. Change to. %s', $homeUrl, $magentoUrl)
				);
			}
		}
		else {
			if (strpos($homeUrl, $magentoUrl) !== 0) {
				IntegrationException::throwException(
					sprintf('Your home URL (%s) is invalid as it does not start with the Magento base URL (%s).', $homeUrl, $magentoUrl)
				);
			}
			
			if ($homeUrl === $magentoUrl) {
				IntegrationException::throwException('Your WordPress Home URL matches your Magento URL. Try changing your Home URL to something like ' . $magentoUrl . '/blog');
			}
		}
		
		return $this;
	}
}
