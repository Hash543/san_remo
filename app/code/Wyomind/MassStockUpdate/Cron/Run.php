<?php
/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Cron;

class Run
{
    public $module = "massstockupdate";

    /**
     * Run constructor.
     * @param \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Wyomind\MassStockUpdate\Logger\LoggerCron $logger
     * @param \Wyomind\Core\Helper\Data $coreHelper
     */
    public function __construct(
        \Wyomind\MassStockUpdate\Model\ResourceModel\Profiles\CollectionFactory $collectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Wyomind\MassStockUpdate\Logger\LoggerCron $logger,
        \Wyomind\Core\Helper\Data $coreHelper
    )
    {
        $this->_collectionFactory = $collectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_coreDate = $coreDate;
        $this->_logger = $logger;
        $this->_transportBuilder = $transportBuilder;
        $this->_coreHelper = $coreHelper;
    }

    /**
     * @param \Magento\Cron\Model\Schedule $schedule
     */
    public function run(\Magento\Cron\Model\Schedule $schedule)
    {
        try {
            $log = [];

            $this->_logger->notice("-------------------- CRON PROCESS --------------------");
            $log[] = "-------------------- CRON PROCESS --------------------";

            $coll = $this->_collectionFactory->create();

            $cnt = 0;

            foreach ($coll as $profile) {

                $done = false;
                try {

                    $this->_logger->notice("--> Running profile : " . $profile->getName() . " [#" . $profile->getId() . "] <--");
                    $log[] = "--> Running profile : " . $profile->getName() . " [#" . $profile->getId() . "] <--";

                    $cron = [];

                    $cron['current']['localDate'] = $this->_coreDate->date('l Y-m-d H:i:s');
                    $cron['current']['gmtDate'] = $this->_coreDate->gmtDate('l Y-m-d H:i:s');
                    $cron['current']['localTime'] = $this->_coreDate->timestamp();
                    $cron['current']['gmtTime'] = $this->_coreDate->gmtTimestamp();

                    $cron['file']['localDate'] = $this->_coreDate->date('l Y-m-d H:i:s', $profile->getImportedAt());
                    $cron['file']['gmtDate'] = $profile->getImportedAt();
                    $cron['file']['localTime'] = $this->_coreDate->timestamp($profile->getImportedAt());
                    $cron['file']['gmtTime'] = strtotime($profile->getImportedAt());


                    $cron['offset'] = $this->_coreDate->getGmtOffset("hours");

                    $log[] = '   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset'] . "";
                    $log[] = '   * Current date : ' . $cron['current']['gmtDate'] . " GMT / " . $cron['current']['localDate'] . ' GMT' . $cron['offset'] . "";
                    $this->_logger->notice('   * Last update : ' . $cron['file']['gmtDate'] . " GMT / " . $cron['file']['localDate'] . ' GMT' . $cron['offset']);
                    $this->_logger->notice('   * Current date : ' . $cron['current']['gmtDate'] . " GMT / " . $cron['current']['localDate'] . ' GMT' . $cron['offset']);

                    $cronExpr = json_decode($profile->getCronSettings());

                    $i = 0;

                    if ($cronExpr != null && isset($cronExpr->days)) {
                        foreach ($cronExpr->days as $d) {
                            foreach ($cronExpr->hours as $h) {
                                $time = explode(':', $h);
                                if (date('l', $cron['current']['gmtTime']) == $d) {
                                    $cron['tasks'][$i]['localTime'] = strtotime($this->_coreDate->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                                    $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                                } else {
                                    $cron['tasks'][$i]['localTime'] = strtotime("last " . $d, $cron['current']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                                    $cron['tasks'][$i]['localDate'] = date('l Y-m-d H:i:s', $cron['tasks'][$i]['localTime']);
                                }

                                if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime'] && $done != true) {
                                    $this->_logger->notice('   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']));
                                    $log[] = '   * Scheduled : ' . ($cron['tasks'][$i]['localDate'] . " GMT" . $cron['offset']) . "";
                                    $this->_logger->notice("   * Starting generation");

                                    $result = $profile->multipleImport();
                                    if ($result) {
                                        $done = true;
                                        $this->_logger->notice("   * EXECUTED!");
                                        $log[] = "   * EXECUTED!";
                                        $log[] = __('The profile %1 [ID:%2] has been processed.', $profile->getName(), $profile->getId());
                                        if (count($result["success"]) > 0) {
                                            $log[] = __('%1', $result["success"]);
                                        }
                                        if (count($result["notice"]) > 0) {
                                            $log[] = __('%1', $result["notice"]);
                                        }
                                        if (count($result["warning"]) > 0) {
                                            $log[] = __('%1', $result["warning"]);
                                        }
                                        
                                    }
                                    $cnt++;
                                    break 2;
                                }

                                $i++;
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $cnt++;
                    $this->_logger->notice("   * ERROR! " . ($e->getMessage()));
                    $log[] = "   * ERROR! " . ($e->getMessage()) . "";
                }
                if (!$done) {
                    $this->_logger->notice("   * SKIPPED!");
                    $log[] = "   * SKIPPED!";
                }
            }


            if ($this->_coreHelper->getStoreConfig($this->module . "/settings/enable_reporting")) {


                $emails = explode(',', $this->_coreHelper->getStoreConfig($this->module . "/settings/emails"));

                if (count($emails) > 0) {
                    try {
                        if ($cnt) {
                            $template = "wyomind_massstockupdate_cron_report";

                            $transport = $this->_transportBuilder
                                    ->setTemplateIdentifier($template)
                                    ->setTemplateOptions(
                                            [
                                                'area' => \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE,
                                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID
                                            ]
                                    )
                                    ->setTemplateVars(
                                            [
                                                'report' => implode("<br/>", $log),
                                                'subject' => $this->_coreHelper->getStoreConfig($this->module . '/settings/report_title')
                                            ]
                                    )
                                    ->setFrom(
                                            [
                                                'email' => $this->_coreHelper->getStoreConfig($this->module . '/settings/sender_email'),
                                                'name' => $this->_coreHelper->getStoreConfig($this->module . '/settings/sender_name')
                                            ]
                                    )
                                    ->addTo($emails[0]);

                            $count = count($emails);
                            for ($i = 1; $i < $count; $i++) {
                                $transport->addCc($emails[$i]);
                            }

                            $transport->getTransport()->sendMessage();
                        }
                    } catch (\Magento\Framework\Exception\LocalizedException $e) {
                        $this->_logger->notice('   * EMAIL ERROR! ' . $e->getMessage());
                        throw new \Magento\Framework\Exception\LocalizedException(__("Error: %s", $e->getMessage()));
                    }
                }
            }
        } catch (\Exception $e) {

            $schedule->setStatus('failed');
            $schedule->setMessage($e->getMessage());
            $schedule->save();
            $this->_logger->notice("CRITICAL ERROR ! ");
            $this->_logger->notice($e->getMessage());
        }
    }
}