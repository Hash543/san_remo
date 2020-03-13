<?php

namespace Alfa9\Treatment\Cron;

class Run
{
    /**
     * @var \Alfa9\Treatment\Helper\TreatmentReminder
     */
    private $treatmentReminder;
    /**
     * @var \Alfa9\Treatment\Helper\TreatmentPoll
     */
    private $treatmentPoll;
    /**
     * @var \Alfa9\Treatment\Helper\TreatmentTips
     */
    private $treatmentTips;

    /**
     * Run constructor.
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Alfa9\Treatment\Helper\TreatmentReminder $treatmentReminder,
        \Alfa9\Treatment\Helper\TreatmentPoll $treatmentPoll,
        \Alfa9\Treatment\Helper\TreatmentTips $treatmentTips
    )
    {
        $this->treatmentReminder = $treatmentReminder;
        $this->treatmentPoll = $treatmentPoll;
        $this->treatmentTips = $treatmentTips;
    }


    public function execute()
    {
        $this->treatmentReminder->getCurrentTreatment();
        $this->treatmentPoll->getTreatmentPoll();
        $this->treatmentTips->getTreatmentTips();
    }
}