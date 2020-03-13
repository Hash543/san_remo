<?php

namespace Pss\CsvProcessor\Cron;

class Run
{
    /**
     * @var \Pss\CsvProcessor\Helper\CsvGenerator
     */
    private $csvGenerator;

    public function __construct(
       \Pss\CsvProcessor\Helper\CsvGenerator $csvGenerator
   )
   {
       $this->csvGenerator = $csvGenerator;
   }

    public function execute() {
        $this->csvGenerator->reGenerateCsv();
    }
}