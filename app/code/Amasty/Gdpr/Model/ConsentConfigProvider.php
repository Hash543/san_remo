<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2019 Amasty (https://www.amasty.com)
 * @package Amasty_Gdpr
 */


namespace Amasty\Gdpr\Model;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConsentConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Checkbox
     */
    private $checkbox;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        Checkbox $checkbox,
        Config $config
    ) {
        $this->checkbox = $checkbox;
        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $agreements = [];

        $agreements['amastyGdprConsent'] = [
            'isEnabled'    => $this->checkbox->isEnabled(Checkbox::AREA_CHECKOUT),
            'isVisible'    => $this->checkbox->isVisible(Checkbox::AREA_CHECKOUT),
            'checkboxText' => $this->checkbox->getConsentText(),
            'visibleInCountries' => $this->config->isSetFlag('privacy_checkbox/eea_only')
                ? $this->config->getEEACountryCodes()
                : false,
        ];

        return $agreements;
    }
}
