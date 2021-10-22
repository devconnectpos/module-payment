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

    public function getPaymentIdByType($type, $registerId = null)
    {
        $payment = $this->getPaymentCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $registerId], ['eq' => 0], ['null' => true]])
            ->addOrder('id')
            ->getFirstItem();

        return $payment->getData('id');
    }

    public function getPaymentDataByType($type, $registerId = null)
    {
        return $this->getPaymentCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $registerId], ['eq' => 0], ['null' => true]])
            ->addOrder('id')
            ->getFirstItem();
    }

    public function getPaymentMethodsOfType($type, $registerId = null)
    {
        $paymentCollection = $this->getPaymentCollection()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter(['register_id', 'register_id', 'register_id'], [['eq' => $registerId], ['eq' => 0], ['null' => true]])
            ->addOrder('id');
        return $paymentCollection->getItems();
    }

    /**
     * @return \SM\Payment\Model\ResourceModel\RetailPayment\Collection
     */
    protected function getPaymentCollection()
    {
        return $this->paymentCollection->create();
    }
}
