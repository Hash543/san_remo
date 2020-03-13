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
use PSS\WordPress\Model\Network;
use PSS\WordPress\Model\WPConfig;

/* Misc */
use PSS\WordPress\Model\Integration\IntegrationException;

class NetworkTest
{
    /*
     * @var Network
     */
    protected $network;

    /*
     * @var WPConfig
     */
    protected $wpConfig;

    /*
     *
     * @param Network $network
     * @param WPConfig $wpConfig
     */
    public function __construct(Network $network, WPConfig $wpConfig)
    {
        $this->network  = $network;
        $this->wpConfig = $wpConfig;
    }

    /*
     * This test checks for the situation where Multisite is enabled in WordPress
     * But the PSS_WordPress_Multisite add-on is not installed in Magento
     *
     * @return $this
     */
    public function runTest()
    {
        if ((int)$this->wpConfig->getData('MULTISITE') === 0) {
            // Multisite not enabled in WordPress
            return $this;
        }

        if ($this->network->isEnabled()) {
            return $this;
        }

        IntegrationException::throwException(sprintf(
            'The WordPress Network is active. You must install the PSS_WordPress_Multisite add-on module. This can be found at %s'
        ));
    }
}
