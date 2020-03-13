<?php
/**
 * @author Israel Yasis
 */
namespace Alfa9\MDirector\Cron;

class Observer
{
    /**
     * @var \Alfa9\MDirector\Helper\Data
     */
    private $helperData;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;
    /**
     * @var \Magento\Newsletter\Model\Subscriber
     */
    private $newsletterSubscriber;
    /**
     * @var \Magento\Framework\App\Config\Storage\WriterInterface
     */
    private $writer;
    /**
     * Observer constructor.
     * @param \Alfa9\MDirector\Helper\Data $helperData
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\Newsletter\Model\Subscriber $newsletterSubscriber
     * @param \Magento\Framework\App\Config\Storage\WriterInterface $writer
     */
    public function __construct(
        \Alfa9\MDirector\Helper\Data $helperData,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Newsletter\Model\Subscriber $newsletterSubscriber,
        \Magento\Framework\App\Config\Storage\WriterInterface $writer) {
        $this->helperData = $helperData;
        $this->date = $date;
        $this->timezone = $timezone;
        $this->newsletterSubscriber = $newsletterSubscriber;
        $this->writer = $writer;
    }

    /**
     * Run process
     *
     * @return $this
     */
    public function run()
    {
        if(!$this->helperData->isUnSubscriptionEnabled()) {
            return $this;
        }

        $currentDate = $this->date->gmtDate();
        $lastSyncDate = $this->helperData->unSubscriptionLastTime();
        if (!$lastSyncDate) {
            $lastSyncDate = '2015-01-01';
        }
        $startDate = $this->timezone->date($lastSyncDate)->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $endDate = $this->timezone->date(new \DateTime())
            ->format(\Magento\Framework\Stdlib\DateTime::DATE_PHP_FORMAT);
        $result = $this->helperData->findByDateUnSubscription($startDate, $endDate);
        if ($result && count($result)) {
            foreach ($result as $item) {
                if(!isset($item['Contact'])) {
                    continue;
                }
                $subscriber = $this->newsletterSubscriber->loadByEmail($item['Contact']);
                if ($subscriber->getId() && $subscriber->getStatus() != \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED) {
                    try {
                        $subscriber->unsubscribe();
                    }catch (\Exception $exception) {
                        continue;
                    }
                }
            }
        }
        $this->writer->save(\Alfa9\MDirector\Helper\Data::CONFIG_UN_SUBSCRIPTION_LAST_TIME, $endDate);
        return $this;
    }
}
