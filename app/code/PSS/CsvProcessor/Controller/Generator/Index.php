<?php

namespace Pss\CsvProcessor\Controller\Generator;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Pss\CsvProcessor\Helper\CsvGenerator
     */
    private $csvGenerator;

    public function __construct(
        Context $context,
        \Pss\CsvProcessor\Helper\CsvGenerator $csvGenerator)
    {
        parent::__construct($context);

        $this->csvGenerator = $csvGenerator;
    }

    // csv/generator
    public function execute()
    {
        return $this->csvGenerator->reGenerateCsv();
    }
}