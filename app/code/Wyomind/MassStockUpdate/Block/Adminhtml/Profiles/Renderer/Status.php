<?php

/**
 * Copyright © 2018 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Block\Adminhtml\Profiles\Renderer;

/**
 * Status renderer
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    public $module = "MassStockUpdate";
    protected $_ioRead = null;
    protected $_coreDate = null;
    protected $_directoryRead = null;

    const _SUCCEEDED = "SUCCEEDED";
    const _PENDING = "PENDING";
    const _PROCESSING = "PROCESSING";
    const _HOLD = "HOLD";
    const _FAILED = "FAILED";

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param array $data
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
    \Magento\Backend\Block\Context $context, \Magento\Framework\Filesystem $filesystem,
            \Magento\Framework\Filesystem\Directory\ReadFactory $directoryRead,
            \Magento\Framework\Stdlib\DateTime\DateTime $coreDate, array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->_ioRead = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_coreDate = $coreDate;
        $this->_directoryRead = $directoryRead->create(".");
    }

    /**
     * Renders grid column
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $class = "\Wyomind\\" . $this->module . "\Helper\Data";
        $this->_flagFile = $class::TMP_FOLDER . $class::TMP_FILE_PREFIX . $row->getName() . ".flag";


        if ($this->_ioRead->isFile($this->_flagFile)) {
            $io = $this->_ioRead->openFile($this->_flagFile, 'r');
            $line = $io->readCsv(0, ";");
            $stats = $io->stat();

            if ($line[0] == self::_SUCCEEDED) {
                $line[0] = $this->checkCronTasks($line[0], $row, $stats["mtime"]);
            }

            switch ($line[0]) {
                case self::_SUCCEEDED:
                    $severity = 'notice';
                    $status = __($line[0]);
                    break;
                case self::_PENDING:
                    $severity = 'minor';
                    $status = __($line[0]);
                    break;
                case self::_PROCESSING:
                    $percent = $line[2];
                    $severity = 'minor';
                    $status = __($line[0]) . " [" . $percent . "%]";
                    break;
                case self::_HOLD:
                    $severity = 'major';
                    $status = __($line[0]);
                    break;
                case self::_FAILED:
                    $severity = 'critical';
                    $status = __($line[0]);
                    break;
                default :
                    $severity = 'critical';
                    $status = __("ERROR");
                    break;
            }
        } else {

            $severity = 'minor';
            $line[1] = "no message";
            $status = __(self::_PENDING);
        }
        $script = "<script language='javascript' type='text/javascript'>var updater_url='"
                . $this->getUrl('*/*/updater') . "'</script>";

        return $script . "<span name='" . $row->getName() . "' title=\"" . strip_tags($line[1]) . "\" class='grid-severity-$severity updater' data-cron='" . $row->getCronSettings()
                . "' id='profile_" . $row->getId() . "'><span>" . ($status) . "</span></span>";
    }

    protected function checkCronTasks($status, \Magento\Framework\DataObject $row, $mtime)
    {
        $cron = array();
        $cron['current']['localTime'] = $this->_coreDate->timestamp();
        $cron['file']['localTime'] = $this->_coreDate->timestamp($mtime);
        $cronExpr = json_decode($row->getCronSettings());
        $i = 0;
        foreach ($cronExpr->days as $day) {
            foreach ($cronExpr->hours as $hour) {
                $time = explode(':', $hour);

                if ($this->_coreDate->date('l') == $day) {
                    $cron['tasks'][$i]['localTime'] = strtotime($this->_coreDate->date('Y-m-d')) + ($time[0] * 60 * 60) + ($time[1] * 60);
                } else {
                    $cron['tasks'][$i]['localTime'] = strtotime("last " . $day, $cron['current']['localTime']) + ($time[0] * 60 * 60) + ($time[1] * 60);
                }

                if ($cron['tasks'][$i]['localTime'] >= $cron['file']['localTime'] && $cron['tasks'][$i]['localTime'] <= $cron['current']['localTime']
                ) {
                    $status = self::_PENDING;
                    continue 2;
                }
                $i++;
            }
        }

        return $status;
    }

}
