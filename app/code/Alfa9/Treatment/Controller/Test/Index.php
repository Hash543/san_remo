<?php

namespace Alfa9\Treatment\Controller\Test;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Index extends Action
{
    /**
     * @var \Alfa9\Treatment\Helper\TreatmentReminder
     */
    private $treatmentReminder;
    /**
     * @var \Alfa9\Treatment\Helper\TreatmentTips
     */
    private $treatmentTips;
    /**
     * @var \Alfa9\Treatment\Helper\TreatmentPoll
     */
    private $treatmentPoll;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Alfa9\Treatment\Helper\TreatmentReminder $treatmentReminder
     */
    public function __construct(
        Context $context,
        \Alfa9\Treatment\Helper\TreatmentReminder $treatmentReminder,
        \Alfa9\Treatment\Helper\TreatmentTips $treatmentTips,
        \Alfa9\Treatment\Helper\TreatmentPoll $treatmentPoll
    )
    {
        parent::__construct($context);
        $this->treatmentReminder = $treatmentReminder;
        $this->treatmentTips = $treatmentTips;
        $this->treatmentPoll = $treatmentPoll;
    }


    public function execute()
    {
        var_dump("== TREATMENT TEST ==");
        $date = $this->getRequest()->getParam('date', null);
        var_dump($date);

        $this->treatmentReminder->getCurrentTreatment($date);
        $this->treatmentPoll->getTreatmentPoll($date);
        $this->treatmentTips->getTreatmentTips($date);
    }
}