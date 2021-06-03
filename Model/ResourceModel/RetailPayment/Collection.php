<?php

namespace SM\Payment\Model\ResourceModel\RetailPayment;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use SM\Payment\Model\RetailPayment as Model;
use SM\Payment\Model\ResourceModel\RetailPayment as ResourceModel;

/**
 * Class Collection
 * @package SM\Payment\Model\ResourceModel\RetailPayment
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(Model::class, ResourceModel::class);
    }
}
