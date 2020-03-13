<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Logger;

class Logger extends \Monolog\Logger
{

    protected $_timezone = null;
    protected $_configHelper = null;
    protected $_consoleOutput = null;

    public function __construct(
        $name,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Wyomind\MassStockUpdate\Helper\Config $configHelper,
        \Symfony\Component\Console\Output\ConsoleOutput $consoleOutput,
        array $handlers = [],
        array $processors = []
    ) {
        $this->_timezone = $timezone;
        $this->_configHelper = $configHelper;
        $this->_consoleOutput = $consoleOutput;
        parent::__construct($name, $handlers, $processors);
    }

    public function notice(
        $message,
        array $context = []
    ) {
        if ($this->_configHelper->getSettingsLog() == 1) {
            if (php_sapi_name() === "cli") {
                if (is_array($message)) {
                    $object = json_encode($message);
                } else {
                    $object = $message;
                }
                $this->_consoleOutput->writeln($this->_timezone->date()->format('Y-m-d H:i:s') . " " . $object);
            }
            return parent::notice($message, $context);
        }
    }

    public function addRecord(
        $level,
        $message,
        array $context = []
    ) {
        if (!$this->handlers) {
            $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG));
        }

        $levelName = static::getLevelName($level);

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        foreach ($this->handlers as $key => $handler) {
            if ($handler->isHandling(['level' => $level])) {
                $handlerKey = $key;
                break;
            }
        }

        if (null === $handlerKey) {
            return false;
        }


        $record = [
            'message' => str_replace("\n", "\n                      ", $message),
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => $this->_timezone->date()->format('Y-m-d H:i:s'),
            'extra' => [],
        ];

        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }

        while (isset($this->handlers[$handlerKey]) &&
        false === $this->handlers[$handlerKey]->handle($record)) {
            $handlerKey++;
        }

        return true;
    }
}
