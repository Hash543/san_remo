<?php
/**
 * @author Cristian Sanclemente <csanclemente@alfa9.com>
 * @copyright Copyright (c) 2017 Alfa9 (http://www.alfa9.com)
 * @package Alfa9
 */

namespace PSS\CRM\Api;

use PSS\CRM\Api\Data\QueueInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface QueueRepositoryInterface 
{
    public function save(QueueInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(QueueInterface $page);

    public function deleteById($id);
}
