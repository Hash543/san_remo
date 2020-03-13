<?php

namespace Pss\CsvProcessor\Controller\Adminhtml\Csv;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;

class Convert extends \Magento\Backend\App\Action
{
    /**
     * @var \Pss\CsvProcessor\Helper\CsvGenerator
     */
    private $csvGenerator;

    public function __construct(
        Action\Context $context,
        \Pss\CsvProcessor\Helper\CsvGenerator $csvGenerator
    ) {
        parent::__construct($context);
        $this->csvGenerator = $csvGenerator;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $res = [];

        /** @var \Magento\Framework\Controller\Result\Json $json */
        $json = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $this->csvGenerator->reGenerateCsv();
            $res['success'] = 'Success';
        } catch (\Exception $exception) {
            $res['error'] = 'Try Again';
        }
        return $json->setData($res);
    }
}
