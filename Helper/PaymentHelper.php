<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 3/5/18
 * Time: 17:01
 */

namespace SM\Payment\Helper;

use SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory;

class PaymentHelper
{

    private $paymentCollection;

    /**
     * PaymentHelper constructor.
     *
     * @param \SM\Payment\Model\ResourceModel\RetailPayment\CollectionFactory $paymentCollection
     */
    public function __construct(
        CollectionFactory $paymentCollection
    ) {
        $this->paymentCollection = $paymentCollection;
    }

    public function getPaymentIdByType($type, $registerId)
    {
        $paymentCollection = $this->getPaymentCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $registerId], ['eq' => 0], ['null' => true]])
            ->addOrder('id')
            ->getFirstItem();

        return $paymentCollection ? $paymentCollection->getData('id') : null;
    }

    public function getPaymentDataByType($type, $registerId)
    {
        $paymentCollection = $this->getPaymentCollection()->addFieldToFilter('type', $type)
            ->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $registerId], ['eq' => 0], ['null' => true]])
            ->addOrder('id')
            ->getFirstItem();

        return $paymentCollection ?: null;
    }

    /**
     * @return \SM\Payment\Model\ResourceModel\RetailPayment\Collection
     */
    protected function getPaymentCollection()
    {
        return $this->paymentCollection->create();
    }
}
