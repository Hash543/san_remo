<?php

namespace Wyomind\MassProductImport\Logger;

class HandlerCron extends \Wyomind\MassStockUpdate\Logger\HandlerCron
{

    public $fileName = '/var/log/MassProductImport-Cron.log';
}
