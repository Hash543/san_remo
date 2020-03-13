<?php
/**
 * Copyright © 2017 Firebear Studio. All rights reserved.
 */
 
namespace Firebear\ConfigurableProducts\Block\Adminhtml\Product\Steps;

use Magento\Ui\Block\Component\StepsWizard\StepAbstract;

class DefaultValues extends StepAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Default Values');
    }
}
