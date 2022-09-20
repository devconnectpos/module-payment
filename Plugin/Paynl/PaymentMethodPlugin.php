<?php

namespace SM\Payment\Plugin\Paynl;

class PaymentMethodPlugin
{
    /**
     * @param \Paynl\Payment\Model\Paymentmethod\PaymentMethod|\Magento\Payment\Model\Method\AbstractMethod $subject
     * @param $result
     * @param \Magento\Framework\DataObject $data
     * @return void
     */
    public function afterAssignData($subject, $result, \Magento\Framework\DataObject $data)
    {
        if ($data instanceof \Magento\Framework\DataObject) {
            $subject->getInfoInstance()->setAdditionalInformation('split_data', json_encode($data->getData('additional_data')));
        }

        return $result;
    }
}
