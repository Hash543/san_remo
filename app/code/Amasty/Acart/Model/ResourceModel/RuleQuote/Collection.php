<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2018 Amasty (https://www.amasty.com)
 * @package Amasty_Acart
 */


namespace Amasty\Acart\Model\ResourceModel\RuleQuote;

use Amasty\Acart\Model\History;
use Amasty\Acart\Model\RuleQuote;

/**
 * @method RuleQuote[] getItems()
 * @method RuleQuote getFirstItem()
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Acart\Model\RuleQuote', 'Amasty\Acart\Model\ResourceModel\RuleQuote');
        $this->_setIdFieldName($this->getResource()->getIdFieldName());
    }

    public function addCompleteFilter()
    {
        $this->getSelect()
            ->joinLeft(
                ['history' => $this->getTable('amasty_acart_history')],
                'main_table.rule_quote_id = history.rule_quote_id AND history.status <> "' . History::STATUS_SENT . '"',
                []
            )
            ->where('main_table.status = ? ', RuleQuote::STATUS_PROCESSING)
            ->group('main_table.rule_quote_id')
            ->having('count(history.rule_quote_id) = 0');

        return $this;
    }
}
